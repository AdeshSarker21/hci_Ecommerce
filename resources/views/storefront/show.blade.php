<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seller->store_name }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name', 'Ecommerce') }}</a>
                <form method="GET" action="{{ route('storefront.show', $seller->store_slug) }}" class="flex-1 max-w-lg mx-8">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in {{ $seller->store_name }}..."
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

    @if($seller->store_banner)
        <div class="h-48 sm:h-64 bg-gray-200 relative overflow-hidden">
            <img src="{{ asset('storage/' . $seller->store_banner) }}" alt="{{ $seller->store_name }} banner" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
        </div>
    @else
        <div class="h-48 sm:h-64 bg-gradient-to-r from-emerald-600 to-teal-500 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-10">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <img src="{{ $seller->logo_url }}" alt="{{ $seller->store_name }}" class="w-20 h-20 rounded-xl border-4 border-white shadow-md">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $seller->store_name }}</h1>
                        @if($seller->is_featured)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Featured Seller
                            </span>
                        @endif
                    </div>
                    @if($seller->store_tagline)
                        <p class="text-gray-500 mt-1">{{ $seller->store_tagline }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-500">
                        @if($stats['average_rating'] > 0)
                            <div class="flex items-center gap-1">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= round($stats['average_rating']) ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <span>{{ number_format($stats['average_rating'], 1) }}</span>
                                <span>({{ $stats['total_reviews'] }} reviews)</span>
                            </div>
                        @endif
                        <span>{{ $stats['product_count'] }} products</span>
                        @if($seller->business_city || $seller->business_country)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ collect([$seller->business_city, $seller->business_country])->filter()->implode(', ') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($seller->contact_website)
                        <a href="{{ $seller->contact_website }}" target="_blank" rel="noopener"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            Website
                        </a>
                    @endif
                    @if($seller->facebook_url)
                        <a href="{{ $seller->facebook_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center p-2 text-gray-500 hover:text-blue-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    @endif
                    @if($seller->instagram_url)
                        <a href="{{ $seller->instagram_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center p-2 text-gray-500 hover:text-pink-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12.017 24 18.635 24 24.001 18.633 24.001 12.013 24.001 5.393 18.635.026 12.017.026V0z"/></svg>
                        </a>
                    @endif
                    @if($seller->youtube_url)
                        <a href="{{ $seller->youtube_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center p-2 text-gray-500 hover:text-red-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
            @if($seller->store_description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $seller->store_description }}</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-8">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-4">Filters</h3>
                    <form method="GET" action="{{ route('storefront.show', $seller->store_slug) }}" class="space-y-5">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                            <select name="category_id" onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label>
                            <select name="brand_id" onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort By</label>
                            <select name="sort" onchange="this.form.submit()"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                            Apply Filters
                        </button>
                    </form>
                </div>

                @if($seller->about_us)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">About Store</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $seller->about_us }}</p>
                    </div>
                @endif

                @if($seller->shipping_policy)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Shipping Policy</h3>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $seller->shipping_policy }}</p>
                    </div>
                @endif

                @if($seller->return_policy)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Return Policy</h3>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $seller->return_policy }}</p>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Products
                        <span class="text-sm font-normal text-gray-500 ml-2">({{ $products->total() }} items)</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($products as $product)
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
                                @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                    <span class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs font-semibold rounded">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 line-clamp-1 group-hover:text-emerald-600 transition-colors">
                                    {{ app()->getLocale() === 'bn' && $product->name_bn ? $product->name_bn : $product->name }}
                                </h3>
                                @if($product->brand)
                                    <p class="text-xs text-gray-500 mt-1">{{ $product->brand->name }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-3">
                                    <div>
                                        <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                        @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                            <span class="text-sm text-gray-400 line-through ml-2">${{ number_format($product->compare_at_price, 2) }}</span>
                                        @endif
                                    </div>
                                    @if($product->manage_stock)
                                        <span class="text-xs {{ $product->quantity > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ $product->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No products found</h3>
                            <p class="text-sm text-gray-500">This store doesn't have any matching products yet.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-16 bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="/" class="text-lg font-bold text-indigo-600">{{ config('app.name', 'Ecommerce') }}</a>
                <p class="text-sm text-gray-500">&copy; {{ date('Y') }} All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
