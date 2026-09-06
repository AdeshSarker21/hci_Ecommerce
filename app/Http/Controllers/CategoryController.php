<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $children = $category->children()->active()->ordered()->get();

        $subCategoryIds = $children->pluck('id')->push($category->id)->toArray();

        $productsQuery = \App\Models\Product::query()
            ->with([
                'seller:id,store_name,store_slug,store_logo',
                'category:id,name,name_bn,slug',
                'brand:id,name,name_bn,slug,logo',
                'images' => fn ($q) => $q->where('is_featured', true)->limit(1),
            ])
            ->where('status', 'published')
            ->where('is_active', true)
            ->whereIn('category_id', $subCategoryIds);

        $activeFilters = collect();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $productsQuery->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('name_bn', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('description_bn', 'like', "%{$keyword}%");
            });
            $activeFilters->push(['key' => 'keyword', 'label' => __('Search') . ': ' . $keyword, 'value' => $keyword]);
        }

        if ($request->filled('brand_id')) {
            $brandIds = (array) $request->brand_id;
            $productsQuery->whereIn('brand_id', $brandIds);
            $brandNames = Brand::whereIn('id', $brandIds)->pluck('name');
            foreach ($brandNames as $bn) {
                $activeFilters->push(['key' => 'brand_id', 'label' => __('Brand') . ': ' . $bn, 'value' => $bn]);
            }
        }

        if ($request->filled('seller_id')) {
            $productsQuery->where('seller_id', $request->seller_id);
            $sellerName = Seller::where('id', $request->seller_id)->value('store_name');
            $activeFilters->push(['key' => 'seller_id', 'label' => __('Seller') . ': ' . $sellerName, 'value' => $request->seller_id]);
        }

        if ($request->filled('min_price')) {
            $productsQuery->where('price', '>=', $request->min_price);
            $activeFilters->push(['key' => 'min_price', 'label' => __('Min') . ': $' . $request->min_price, 'value' => $request->min_price]);
        }

        if ($request->filled('max_price')) {
            $productsQuery->where('price', '<=', $request->max_price);
            $activeFilters->push(['key' => 'max_price', 'label' => __('Max') . ': $' . $request->max_price, 'value' => $request->max_price]);
        }

        if ($request->filled('in_stock')) {
            $productsQuery->where(function (Builder $q) {
                $q->where('manage_stock', false)
                  ->orWhereColumn('quantity', '>', 'reserved_quantity');
            });
            $activeFilters->push(['key' => 'in_stock', 'label' => __('In Stock'), 'value' => '1']);
        }

        if ($request->filled('is_featured')) {
            $productsQuery->where('is_featured', true);
            $activeFilters->push(['key' => 'is_featured', 'label' => __('Featured'), 'value' => '1']);
        }

        if ($request->filled('attribute_values')) {
            foreach ($request->attribute_values as $attributeId => $value) {
                if (!empty($value)) {
                    $productsQuery->whereHas('attributeValues', function (Builder $avq) use ($attributeId, $value) {
                        $avq->where('attribute_id', $attributeId)->where('value', $value);
                    });
                    $attr = Attribute::find($attributeId);
                    if ($attr) {
                        $activeFilters->push(['key' => "attribute_values[{$attributeId}]", 'label' => $attr->name . ': ' . $value, 'value' => $value]);
                    }
                }
            }
        }

        $sort = $request->sort ?? 'relevance';
        $productsQuery = $this->applySorting($productsQuery, $sort, $category->id);

        $products = $productsQuery->paginate(24)->withQueryString();

        $priceRange = [
            'min' => (clone $productsQuery)->withoutPagination()->min('price'),
            'max' => (clone $productsQuery)->withoutPagination()->max('price'),
        ];

        $brands = Brand::active()->ordered()
            ->whereHas('products', function (Builder $q) use ($subCategoryIds) {
                $q->where('status', 'published')
                  ->where('is_active', true)
                  ->whereIn('category_id', $subCategoryIds);
            })
            ->withCount(['products' => function (Builder $q) use ($subCategoryIds) {
                $q->where('status', 'published')
                  ->where('is_active', true)
                  ->whereIn('category_id', $subCategoryIds);
            }])
            ->get();

        $sellers = Seller::approved()
            ->whereHas('products', function (Builder $q) use ($subCategoryIds) {
                $q->where('status', 'published')
                  ->where('is_active', true)
                  ->whereIn('category_id', $subCategoryIds);
            })
            ->withCount(['products' => function (Builder $q) use ($subCategoryIds) {
                $q->where('status', 'published')
                  ->where('is_active', true)
                  ->whereIn('category_id', $subCategoryIds);
            }])
            ->get();

        $filterableAttributes = Attribute::active()->filterable()->ordered()
            ->whereHas('categories', fn ($q) => $q->whereIn('category_id', $subCategoryIds))
            ->with(['values' => function ($q) use ($subCategoryIds) {
                $q->whereHas('productValues', function ($pvq) use ($subCategoryIds) {
                    $pvq->whereHas('product', function ($pq) use ($subCategoryIds) {
                        $pq->where('status', 'published')
                           ->where('is_active', true)
                           ->whereIn('category_id', $subCategoryIds);
                    });
                });
            }])
            ->get();

        $breadcrumbs = $category->getAncestors()->push($category);

        return view('category.show', compact(
            'category', 'children', 'products', 'brands', 'sellers',
            'filterableAttributes', 'priceRange', 'activeFilters', 'breadcrumbs', 'sort'
        ));
    }

    private function applySorting(Builder $query, string $sort, int $categoryId): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->latest(),
            'rating' => $query->join('sellers', 'products.seller_id', '=', 'sellers.id')
                ->orderByDesc('sellers.average_rating')
                ->select('products.*'),
            'popularity' => $query->withCount('orders as sales_count')
                ->orderByDesc('sales_count')
                ->latest(),
            default => $query->orderByDesc('is_featured')->latest(),
        };
    }
}
