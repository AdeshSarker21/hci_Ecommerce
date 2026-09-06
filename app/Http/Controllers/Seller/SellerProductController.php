<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SellerProductController extends Controller
{
    private function authorizeSeller()
    {
        $seller = auth()->user()->seller;
        if (!$seller || !$seller->isApproved()) {
            return null;
        }
        return $seller;
    }

    public function index(Request $request)
    {
        $seller = $this->authorizeSeller();
        if (!$seller) {
            return redirect()->route('seller.dashboard');
        }

        $query = Product::where('seller_id', $seller->id)
            ->with(['category', 'brand', 'images' => function ($q) {
                $q->where('is_featured', true)->limit(1);
            }]);

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

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $sort = $request->input('sort', 'newest');
        $query = match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'stock_asc' => $query->orderBy('quantity', 'asc'),
            'stock_desc' => $query->orderBy('quantity', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(15)->withQueryString();

        $categories = Category::active()->ordered()->get();

        $stats = [
            'total' => Product::where('seller_id', $seller->id)->count(),
            'published' => Product::where('seller_id', $seller->id)->where('status', 'published')->count(),
            'draft' => Product::where('seller_id', $seller->id)->where('status', 'draft')->count(),
            'pending_review' => Product::where('seller_id', $seller->id)->where('status', 'pending_review')->count(),
            'approved' => Product::where('seller_id', $seller->id)->where('status', 'approved')->count(),
            'rejected' => Product::where('seller_id', $seller->id)->where('status', 'rejected')->count(),
        ];

        return view('seller.products.index', compact('seller', 'products', 'categories', 'stats'));
    }

    public function create()
    {
        $seller = $this->authorizeSeller();
        if (!$seller) {
            return redirect()->route('seller.dashboard');
        }

        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();
        $warehouses = $seller->warehouses()->active()->get();

        return view('seller.products.create', compact('seller', 'categories', 'brands', 'warehouses'));
    }

    public function store(Request $request)
    {
        $seller = $this->authorizeSeller();
        if (!$seller) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'description_bn' => ['nullable', 'string', 'max:10000'],
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
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'shipping_class' => ['nullable', 'string', 'max:50'],
            'spec' => ['nullable', 'array'],
            'spec.*' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'featured_image_index' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request, $seller, &$product) {
            $productData = collect($validated)->only([
                'name', 'name_bn', 'description', 'description_bn',
                'category_id', 'brand_id', 'type', 'sku',
                'price', 'compare_at_price', 'cost_price',
                'quantity', 'manage_stock', 'low_stock_threshold',
                'is_active', 'is_featured',
                'meta_title', 'meta_description',
                'weight', 'length', 'width', 'height', 'shipping_class',
            ])->toArray();

            $productData['seller_id'] = $seller->id;
            $productData['status'] = 'draft';
            $productData['manage_stock'] = $productData['manage_stock'] ?? true;
            $productData['quantity'] = $productData['quantity'] ?? 0;
            $productData['low_stock_threshold'] = $productData['low_stock_threshold'] ?? 5;

            $product = Product::create($productData);

            $this->saveAttributeValues($product, $request->input('spec', []));
            $this->handleImageUploads($product, $request);
        });

        return redirect()->route('seller.products.show', $product)
            ->with('success', 'Product created successfully as draft.');
    }

    public function show(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        $product->load(['category', 'brand', 'images', 'attributeValues.attribute', 'moderations' => function ($q) {
            $q->latest()->limit(5);
        }]);

        return view('seller.products.show', compact('seller', 'product'));
    }

    public function edit(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        if ($product->status === 'published') {
            return back()->with('error', 'Cannot edit a published product. Unpublish it first.');
        }

        $product->load(['images', 'attributeValues']);

        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();
        $warehouses = $seller->warehouses()->active()->get();

        return view('seller.products.edit', compact('seller', 'product', 'categories', 'brands', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        if ($product->status === 'published') {
            return back()->with('error', 'Cannot edit a published product. Unpublish it first.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'description_bn' => ['nullable', 'string', 'max:10000'],
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
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'shipping_class' => ['nullable', 'string', 'max:50'],
            'spec' => ['nullable', 'array'],
            'spec.*' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'featured_image_index' => ['nullable', 'integer', 'min:0'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['integer', 'exists:product_images,id'],
            'image_alt_texts' => ['nullable', 'array'],
            'image_alt_texts.*' => ['nullable', 'string', 'max:255'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ]);

        DB::transaction(function () use ($validated, $request, $product) {
            $productData = collect($validated)->only([
                'name', 'name_bn', 'description', 'description_bn',
                'category_id', 'brand_id', 'type', 'sku',
                'price', 'compare_at_price', 'cost_price',
                'quantity', 'manage_stock', 'low_stock_threshold',
                'is_active', 'is_featured',
                'meta_title', 'meta_description',
                'weight', 'length', 'width', 'height', 'shipping_class',
            ])->toArray();

            $product->update($productData);

            $this->saveAttributeValues($product, $request->input('spec', []));

            if ($request->has('delete_images')) {
                foreach ($request->input('delete_images', []) as $imageId) {
                    $image = $product->images()->find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                    }
                }
            }

            $this->handleImageUploads($product, $request);
        });

        return back()->with('success', 'Product updated successfully.');
    }

    public function submitForReview(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        if (!in_array($product->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected products can be submitted for review.');
        }

        $previousStatus = $product->status;
        $product->submitForReview();
        $product->logModeration('submit_for_review', null, $previousStatus, 'pending_review');

        return back()->with('success', 'Product submitted for review.');
    }

    public function duplicate(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        $newProduct = DB::transaction(function () use ($product, $seller) {
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->name_bn = $product->name_bn ? $product->name_bn . ' (কপি)' : null;
            $newProduct->slug = null;
            $newProduct->sku = null;
            $newProduct->barcode = null;
            $newProduct->status = 'draft';
            $newProduct->rejection_reason = null;
            $newProduct->published_at = null;
            $newProduct->approved_at = null;
            $newProduct->is_active = true;
            $newProduct->is_featured = false;
            $newProduct->seller_id = $seller->id;
            $newProduct->save();

            foreach ($product->images as $image) {
                $newProduct->images()->create([
                    'path' => $image->path,
                    'alt_text' => $image->alt_text,
                    'sort_order' => $image->sort_order,
                    'is_featured' => $image->is_featured,
                ]);
            }

            foreach ($product->attributeValues as $attrValue) {
                $newProduct->attributeValues()->create([
                    'attribute_id' => $attrValue->attribute_id,
                    'value' => $attrValue->value,
                ]);
            }

            return $newProduct;
        });

        return redirect()->route('seller.products.edit', $newProduct)
            ->with('success', 'Product duplicated successfully. Edit the copy before submitting for review.');
    }

    public function publish(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        if ($product->status !== 'approved') {
            return back()->with('error', 'Only approved products can be published.');
        }

        $product->publish();
        $product->logModeration('publish', null, 'approved', 'published');

        return back()->with('success', 'Product "' . $product->name . '" has been published.');
    }

    public function unpublish(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        if ($product->status !== 'published') {
            return back()->with('error', 'Only published products can be unpublished.');
        }

        $product->unpublish();
        $product->logModeration('unpublish', null, 'published', 'approved');

        return back()->with('success', 'Product "' . $product->name . '" has been unpublished.');
    }

    public function destroy(Product $product)
    {
        $seller = $this->authorizeSeller();
        if (!$seller || $product->seller_id !== $seller->id) {
            abort(403);
        }

        if ($product->status === 'published') {
            return back()->with('error', 'Cannot delete a published product. Unpublish it first.');
        }

        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->delete();
        });

        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function getCategoryAttributes(Category $category)
    {
        $seller = $this->authorizeSeller();
        if (!$seller) {
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

    private function handleImageUploads(Product $product, Request $request): void
    {
        if ($request->hasFile('images')) {
            $featuredIndex = $request->input('featured_image_index', 0);
            $altTexts = $request->input('image_alt_texts', []);

            foreach ($request->file('images') as $index => $imageFile) {
                $path = $imageFile->store('product-images', 'public');

                $product->images()->create([
                    'path' => $path,
                    'alt_text' => $altTexts[$index] ?? null,
                    'sort_order' => $index,
                    'is_featured' => ($index === $featuredIndex),
                ]);
            }
        }

        if ($request->has('existing_images')) {
            $existingIds = $request->input('existing_images', []);
            $featuredIndex = $request->input('featured_image_index', null);
            $altTexts = $request->input('image_alt_texts', []);

            foreach ($existingIds as $position => $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    $image->update([
                        'sort_order' => $position,
                        'is_featured' => ($featuredIndex !== null && $featuredIndex == $position),
                        'alt_text' => $altTexts[$position] ?? $image->alt_text,
                    ]);
                }
            }
        }
    }
}
