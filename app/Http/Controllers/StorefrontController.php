<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $seller = Seller::where('store_slug', $slug)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->firstOrFail();

        $query = Product::where('seller_id', $seller->id)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with(['category', 'brand', 'images']);

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

        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        $sort = $request->input('sort', 'newest');
        $query = match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::active()->ordered()->get()
            ->filter(fn ($cat) => Product::where('seller_id', $seller->id)
                ->where('status', 'published')
                ->where('category_id', $cat->id)
                ->exists());

        $brands = $seller->products()
            ->where('status', 'published')
            ->whereNotNull('brand_id')
            ->with('brand')
            ->get()
            ->pluck('brand')
            ->unique('id')
            ->values();

        $stats = [
            'product_count' => $seller->products()->where('status', 'published')->count(),
            'average_rating' => $seller->average_rating,
            'total_reviews' => $seller->total_reviews,
            'total_sales' => $seller->total_sales,
        ];

        return view('storefront.show', compact('seller', 'products', 'categories', 'brands', 'stats'));
    }
}
