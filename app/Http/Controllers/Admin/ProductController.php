<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Notifications\ProductStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'brand']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('seller', fn ($sq) => $sq->where('store_name', 'like', "%{$search}%"));
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();
        $sellers = Seller::approved()->get();
        $warehouses = \App\Models\Warehouse::active()->get();

        return view('admin.products.create', compact('categories', 'brands', 'sellers', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'description_bn' => ['nullable', 'string', 'max:10000'],
            'seller_id' => ['required', 'exists:sellers,id'],
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

        DB::transaction(function () use ($validated, $request, &$product) {
            $productData = collect($validated)->only([
                'name', 'name_bn', 'description', 'description_bn',
                'seller_id', 'category_id', 'brand_id', 'type', 'sku',
                'price', 'compare_at_price', 'cost_price',
                'quantity', 'manage_stock', 'low_stock_threshold',
                'is_active', 'is_featured',
                'meta_title', 'meta_description',
                'weight', 'length', 'width', 'height', 'shipping_class',
            ])->toArray();

            $productData['status'] = 'draft';
            $productData['manage_stock'] = $productData['manage_stock'] ?? true;
            $productData['quantity'] = $productData['quantity'] ?? 0;
            $productData['low_stock_threshold'] = $productData['low_stock_threshold'] ?? 5;

            $product = Product::create($productData);

            $this->saveAttributeValues($product, $request->input('spec', []));
            $this->handleImageUploads($product, $request);
        });

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product created successfully as draft.');
    }

    public function show(Product $product)
    {
        $product->load(['seller', 'category', 'brand', 'images']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'attributeValues']);

        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();
        $sellers = Seller::approved()->get();
        $warehouses = \App\Models\Warehouse::active()->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'sellers', 'warehouses'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'description_bn' => ['nullable', 'string', 'max:10000'],
            'seller_id' => ['required', 'exists:sellers,id'],
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
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ]);

        DB::transaction(function () use ($validated, $request, $product) {
            $productData = collect($validated)->only([
                'name', 'name_bn', 'description', 'description_bn',
                'seller_id', 'category_id', 'brand_id', 'type', 'sku',
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

    public function approve(Product $product)
    {
        $previousStatus = $product->status;

        if (!in_array($previousStatus, ['pending_review', 'rejected'])) {
            return back()->with('error', 'Only pending review or rejected products can be approved.');
        }

        $product->approve();
        $product->logModeration('approve', null, $previousStatus, 'approved');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'approved')
        );

        return back()->with('success', 'Product "' . $product->name . '" has been approved.');
    }

    public function reject(Request $request, Product $product)
    {
        $previousStatus = $product->status;

        if (!in_array($previousStatus, ['pending_review', 'approved'])) {
            return back()->with('error', 'Only pending review or approved products can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $product->reject($validated['rejection_reason']);
        $product->logModeration('reject', $validated['rejection_reason'], $previousStatus, 'rejected');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'rejected', $validated['rejection_reason'])
        );

        return back()->with('success', 'Product "' . $product->name . '" has been rejected.');
    }

    public function publish(Product $product)
    {
        $previousStatus = $product->status;

        if ($previousStatus !== 'approved') {
            return back()->with('error', 'Only approved products can be published.');
        }

        $product->publish();
        $product->logModeration('publish', null, $previousStatus, 'published');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'published')
        );

        return back()->with('success', 'Product "' . $product->name . '" has been published.');
    }

    public function unpublish(Product $product)
    {
        $previousStatus = $product->status;

        if ($previousStatus !== 'published') {
            return back()->with('error', 'Only published products can be unpublished.');
        }

        $product->unpublish();
        $product->logModeration('unpublish', null, $previousStatus, 'approved');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'approved')
        );

        return back()->with('success', 'Product "' . $product->name . '" has been unpublished.');
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
