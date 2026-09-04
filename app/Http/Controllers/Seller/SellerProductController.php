<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;

class SellerProductController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $query = Product::where('seller_id', $seller->id)->with(['category', 'brand']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('seller.products.index', compact('seller', 'products'));
    }

    public function create()
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();

        return view('seller.products.create', compact('seller', 'categories', 'brands'));
    }

    public function store(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'description_bn' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'type' => ['required', 'string', 'in:physical,digital,service'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'manage_stock' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['seller_id'] = $seller->id;
        $validated['status'] = 'draft';
        $validated['manage_stock'] = $validated['manage_stock'] ?? true;
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 5;

        $product = Product::create($validated);

        $this->saveAttributeValues($product, $request->input('spec', []));

        return redirect()->route('seller.products.show', $product)
            ->with('success', 'Product created successfully as draft.');
    }

    public function show(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        $product->load(['category', 'brand']);

        return view('seller.products.show', compact('seller', 'product'));
    }

    public function edit(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        if (in_array($product->status, ['published'])) {
            return back()->with('error', 'Cannot edit a published product. Unpublish it first.');
        }

        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();

        return view('seller.products.edit', compact('seller', 'product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        if (in_array($product->status, ['published'])) {
            return back()->with('error', 'Cannot edit a published product. Unpublish it first.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'description_bn' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'type' => ['sometimes', 'string', 'in:physical,digital,service'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'manage_stock' => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $product->update($validated);

        $this->saveAttributeValues($product, $request->input('spec', []));

        return back()->with('success', 'Product updated successfully.');
    }

    public function submitForReview(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        if (!in_array($product->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected products can be submitted for review.');
        }

        $product->submitForReview();

        return back()->with('success', 'Product submitted for review.');
    }

    public function destroy(Product $product)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($product->seller_id !== $seller->id) {
            abort(403);
        }

        if (in_array($product->status, ['published'])) {
            return back()->with('error', 'Cannot delete a published product. Unpublish it first.');
        }

        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function getCategoryAttributes(Category $category)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attributes = $category->attributes()
            ->with(['values' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->orderByPivot('sort_order')
            ->get()
            ->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'name_bn' => $attr->name_bn,
                    'type' => $attr->type,
                    'is_required' => $attr->pivot->is_required,
                    'values' => $attr->values->map(fn($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                        'value_bn' => $v->value_bn,
                    ]),
                ];
            });

        return response()->json($attributes);
    }

    private function saveAttributeValues(Product $product, array $attributeData): void
    {
        $product->attributeValues()->delete();

        if (empty($attributeData)) {
            return;
        }

        foreach ($attributeData as $attributeId => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            if (empty($value)) {
                continue;
            }

            $product->attributeValues()->create([
                'attribute_id' => $attributeId,
                'value' => $value,
            ]);
        }
    }
}
