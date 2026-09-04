<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - {{ config('app.name', 'Ecommerce') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="text-xl font-bold text-indigo-600">{{ config('app.name', 'Ecommerce') }}</a>
                <form method="GET" action="{{ route('search') }}" class="flex-1 max-w-lg mx-8">
                    <div class="relative">
                        <input type="text" name="keyword" placeholder="Search products..."
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
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
            <a href="/" class="hover:text-indigo-600">Home</a>
            <span>/</span>
            <a href="{{ route('search') }}" class="hover:text-indigo-600">Products</a>
            <span>/</span>
            @if($product->category)
                <a href="{{ route('search', ['category_id' => $product->category_id]) }}" class="hover:text-indigo-600">{{ $product->category->name }}</a>
                <span>/</span>
            @endif
            <span class="text-gray-900">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    @if($product->primary_image)
                        <img src="{{ asset('storage/' . $product->primary_image) }}" alt="{{ $product->name }}"
                             class="w-full aspect-square object-cover">
                    @else
                        <div class="w-full aspect-square bg-gray-100 flex items-center justify-center text-gray-400">
                            <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    @if($product->brand)
                        <p class="text-sm text-indigo-600 font-medium mb-1">{{ $product->brand->name }}</p>
                    @endif
                    <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                    @if($product->name_bn)
                        <p class="text-sm text-gray-500 mt-1">{{ $product->name_bn }}</p>
                    @endif
                </div>

                <div class="flex items-baseline space-x-3">
                    <span class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                    @if($product->compare_price && $product->compare_price > $product->price)
                        <span class="text-lg text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                        <span class="text-sm font-medium text-emerald-600">Save {{ round((1 - $product->price / $product->compare_price) * 100) }}%</span>
                    @endif
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm {{ $product->stock_quantity > 0 ? 'text-emerald-600 font-medium' : 'text-red-500 font-medium' }}">
                        @if($product->stock_quantity > 0)
                            In Stock ({{ $product->stock_quantity }} available)
                        @else
                            Out of Stock
                        @endif
                    </span>
                    @if($product->is_featured)
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded">Featured</span>
                    @endif
                </div>

                @if($product->description)
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Description</h3>
                        <div class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</div>
                    </div>
                @endif

                @if($product->attributeValues && $product->attributeValues->count() > 0)
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Specifications</h3>
                        <dl class="space-y-2">
                            @foreach($product->attributeValues as $attrValue)
                                <div class="flex">
                                    <dt class="text-sm text-gray-500 w-40 flex-shrink-0">{{ $attrValue->attribute->name }}:</dt>
                                    <dd class="text-sm text-gray-900 font-medium">{{ $attrValue->display_value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Seller</h3>
                    @if($product->seller)
                        <p class="text-sm text-gray-600">{{ $product->seller->store_name ?? $product->seller->name }}</p>
                    @else
                        <p class="text-sm text-gray-500">Marketplace</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
