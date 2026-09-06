<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Services\DatabaseSearchService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchController extends Controller
{
    public function __construct(
        private DatabaseSearchService $searchService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'keyword', 'category_id', 'brand_id', 'seller_id',
            'min_price', 'max_price', 'in_stock', 'is_featured',
            'attribute_values', 'sort', 'per_page',
        ]);

        $results = $this->searchService->search($filters);

        $categories = Category::active()->ordered()->get();
        $brands = Brand::active()->ordered()->get();

        $sellers = Seller::approved()
            ->whereHas('products', fn (Builder $q) => $q->where('status', 'published')->where('is_active', true))
            ->withCount(['products' => fn (Builder $q) => $q->where('status', 'published')->where('is_active', true)])
            ->get();

        $priceRange = [
            'min' => Product::where('status', 'published')->where('is_active', true)->min('price'),
            'max' => Product::where('status', 'published')->where('is_active', true)->max('price'),
        ];

        $activeFilters = collect();
        if (!empty($filters['keyword'])) {
            $activeFilters->push(['key' => 'keyword', 'label' => __('Search') . ': ' . $filters['keyword'], 'value' => $filters['keyword']]);
        }
        if (!empty($filters['category_id'])) {
            $catIds = (array) $filters['category_id'];
            $catNames = Category::whereIn('id', $catIds)->pluck('name');
            foreach ($catNames as $cn) {
                $activeFilters->push(['key' => 'category_id', 'label' => __('Category') . ': ' . $cn, 'value' => $cn]);
            }
        }
        if (!empty($filters['brand_id'])) {
            $brandIds = (array) $filters['brand_id'];
            $brandNames = Brand::whereIn('id', $brandIds)->pluck('name');
            foreach ($brandNames as $bn) {
                $activeFilters->push(['key' => 'brand_id', 'label' => __('Brand') . ': ' . $bn, 'value' => $bn]);
            }
        }
        if (!empty($filters['seller_id'])) {
            $sellerName = Seller::where('id', $filters['seller_id'])->value('store_name');
            $activeFilters->push(['key' => 'seller_id', 'label' => __('Seller') . ': ' . $sellerName, 'value' => $filters['seller_id']]);
        }
        if (!empty($filters['min_price'])) {
            $activeFilters->push(['key' => 'min_price', 'label' => __('Min') . ': $' . $filters['min_price'], 'value' => $filters['min_price']]);
        }
        if (!empty($filters['max_price'])) {
            $activeFilters->push(['key' => 'max_price', 'label' => __('Max') . ': $' . $filters['max_price'], 'value' => $filters['max_price']]);
        }
        if (!empty($filters['in_stock'])) {
            $activeFilters->push(['key' => 'in_stock', 'label' => __('In Stock'), 'value' => '1']);
        }
        if (!empty($filters['is_featured'])) {
            $activeFilters->push(['key' => 'is_featured', 'label' => __('Featured'), 'value' => '1']);
        }

        return view('search', compact('results', 'categories', 'brands', 'sellers', 'priceRange', 'filters', 'activeFilters'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with([
                'seller:id,store_name,store_slug,store_logo,store_banner,average_rating,total_reviews,total_sales,is_featured,store_description,store_description_bn,contact_email,contact_phone,business_address,business_city,business_country,shipping_policy,return_policy,about_us,about_us_bn',
                'category:id,name,name_bn,slug,description,description_bn,image',
                'brand:id,name,name_bn,slug,logo',
                'images:id,product_id,path,alt_text,sort_order,is_featured',
                'attributeValues' => function ($q) {
                    $q->with(['attribute:id,name,name_bn,slug,type,is_filterable,is_variant,sort_order', 'attributeValue:id,attribute_id,value,value_bn,slug,color_code,sort_order']);
                },
            ])
            ->firstOrFail();

        $locale = app()->getLocale();
        $name = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
        $description = $locale === 'bn' && $product->description_bn ? $product->description_bn : $product->description;
        $brandName = $locale === 'bn' && $product->brand?->name_bn ? $product->brand->name_bn : $product->brand?->name;
        $categoryName = $locale === 'bn' && $product->category?->name_bn ? $product->category->name_bn : $product->category?->name;

        $breadcrumbs = collect();
        if ($product->category) {
            $breadcrumbs = $product->category->getAncestors()->push($product->category);
        }

        $reviews = $product->approvedReviews()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(5);

        $ratingDistribution = $product->approvedReviews()
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviews = array_sum($ratingDistribution);
        $averageRating = $totalReviews > 0
            ? round($product->approvedReviews()->avg('rating'), 1)
            : 0;

        $variantAttributes = $product->attributeValues
            ->filter(fn ($av) => $av->attribute?->is_variant)
            ->groupBy(fn ($av) => $av->attribute_id)
            ->map(function ($group) {
                return [
                    'attribute' => $group->first()->attribute,
                    'values' => $group->pluck('attributeValue')->filter()->values(),
                ];
            })
            ->values();

        $relatedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['seller:id,store_name,store_slug', 'category:id,name,name_bn,slug', 'brand:id,name,name_bn,slug,logo', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->limit(8)
            ->get();

        $recommendedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('brand_id', $product->brand_id)
            ->with(['seller:id,store_name,store_slug', 'category:id,name,name_bn,slug', 'brand:id,name,name_bn,slug,logo', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->limit(8)
            ->get();

        if ($recommendedProducts->count() < 4) {
            $additional = Product::active()
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id')->merge($recommendedProducts->pluck('id'))->push($product->id)->toArray())
                ->with(['seller:id,store_name,store_slug', 'category:id,name,name_bn,slug', 'brand:id,name,name_bn,slug,logo', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
                ->limit(8 - $recommendedProducts->count())
                ->get();
            $recommendedProducts = $recommendedProducts->concat($additional);
        }

        return view('product.show', compact(
            'product', 'name', 'description', 'brandName', 'categoryName',
            'breadcrumbs', 'reviews', 'ratingDistribution', 'totalReviews', 'averageRating',
            'variantAttributes', 'relatedProducts', 'recommendedProducts'
        ));
    }
}
