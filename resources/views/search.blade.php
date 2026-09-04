<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }} - Search Products</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name', 'Ecommerce') }}</a>
                <form method="GET" action="{{ route('search') }}" class="flex-1 max-w-lg mx-8">
                    <div class="relative">
                        <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="Search products..."
                               class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-8">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Filters</h3>
                    <form method="GET" action="{{ route('search') }}" id="filterForm" class="space-y-5">
                        @if(!empty($filters['keyword']))
                            <input type="hidden" name="keyword" value="{{ $filters['keyword'] }}">
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                            <select name="category_id" onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label>
                            <select name="brand_id" onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ ($filters['brand_id'] ?? '') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Price Range</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="Min"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="Max"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort By</label>
                            <select name="sort" onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="newest" {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                                <option value="popularity" {{ ($filters['sort'] ?? '') === 'popularity' ? 'selected' : '' }}>Most Popular</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="in_stock" value="1" {{ !empty($filters['in_stock']) ? 'checked' : '' }}
                                       onchange="this.form.submit()" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">In Stock Only</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="is_featured" value="1" {{ !empty($filters['is_featured']) ? 'checked' : '' }}
                                       onchange="this.form.submit()" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Featured Only</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-lg font-semibold text-gray-900">
                        @if(!empty($filters['keyword']))
                            Search results for "{{ $filters['keyword'] }}"
                        @else
                            All Products
                        @endif
                        <span class="text-sm font-normal text-gray-500 ml-2">({{ $results->total() }} results)</span>
                    </h1>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($results as $product)
                        <a href="{{ route('product.show', $product->slug) }}"
                           class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
                            <div class="aspect-square bg-gray-100 relative overflow-hidden">
                                @if($product->primary_image)
                                    <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                @if($product->is_featured)
                                    <span class="absolute top-2 left-2 px-2 py-1 bg-amber-500 text-white text-xs font-semibold rounded">Featured</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">{{ $product->name }}</h3>
                                @if($product->brand)
                                    <p class="text-xs text-gray-500 mt-1">{{ $product->brand->name }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-3">
                                    <div>
                                        <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                        @if($product->compare_price && $product->compare_price > $product->price)
                                            <span class="text-sm text-gray-400 line-through ml-2">${{ number_format($product->compare_price, 2) }}</span>
                                        @endif
                                    </div>
                                    <span class="text-xs {{ $product->stock_quantity > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No products found</h3>
                            <p class="text-sm text-gray-500">Try adjusting your search or filters.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $results->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
