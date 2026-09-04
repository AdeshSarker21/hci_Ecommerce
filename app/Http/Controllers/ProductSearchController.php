<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Services\DatabaseSearchService;
use Illuminate\Http\Request;

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

        $priceRange = [
            'min' => \App\Models\Product::where('status', 'published')->where('is_active', true)->min('price'),
            'max' => \App\Models\Product::where('status', 'published')->where('is_active', true)->max('price'),
        ];

        return view('search', compact('results', 'categories', 'brands', 'priceRange', 'filters'));
    }

    public function show(string $slug)
    {
        $product = \App\Models\Product::where('slug', $slug)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with(['seller', 'category', 'brand', 'attributeValues.attribute'])
            ->firstOrFail();

        return view('product.show', compact('product'));
    }
}
