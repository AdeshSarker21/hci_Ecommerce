<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = Category::whereNull('parent_id')
            ->active()
            ->ordered()
            ->withCount('children')
            ->get();

        $featuredProducts = Product::active()
            ->featured()
            ->with(['seller', 'category', 'brand', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->limit(8)
            ->get();

        $newProducts = Product::active()
            ->with(['seller', 'category', 'brand', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->latest()
            ->limit(8)
            ->get();

        $trendingProducts = Product::active()
            ->with(['seller', 'category', 'brand', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->orderByDesc('is_featured')
            ->limit(8)
            ->get();

        $flashSaleProducts = Product::active()
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->with(['seller', 'category', 'brand', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->orderByRaw('(compare_at_price - price) / compare_at_price DESC')
            ->limit(8)
            ->get();

        $bestSellingProducts = Product::active()
            ->with(['seller', 'category', 'brand', 'images' => fn ($q) => $q->where('is_featured', true)->limit(1)])
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->limit(8)
            ->get();

        $featuredSellers = Seller::approved()
            ->featured()
            ->withCount('products')
            ->limit(6)
            ->get();

        $topBrands = \App\Models\Brand::active()
            ->ordered()
            ->limit(8)
            ->get();

        $wishlistedIds = auth()->check()
            ? \App\Models\Wishlist::where('user_id', auth()->id())->pluck('product_id')->toArray()
            : [];

        return view('welcome', compact(
            'categories',
            'featuredProducts',
            'newProducts',
            'trendingProducts',
            'flashSaleProducts',
            'bestSellingProducts',
            'featuredSellers',
            'topBrands',
            'wishlistedIds'
        ));
    }
}
