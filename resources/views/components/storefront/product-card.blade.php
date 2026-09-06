@props(['product', 'showSeller' => false])

@php
    $discount = $product->discount_percentage;
    $inStock = $product->inStock();
    $img = $product->primary_image ? asset('storage/' . $product->primary_image) : null;
    $locale = app()->getLocale();
    $name = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
    $brandName = $locale === 'bn' && $product->brand?->name_bn ? $product->brand->name_bn : $product->brand?->name;
    $rating = $product->seller?->average_rating ?? 0;
    $reviewCount = $product->seller?->total_reviews ?? 0;
@endphp

<div class="product-card group relative bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-500 hover:-translate-y-1" data-gsap="product">
    {{-- Image --}}
    <div class="relative aspect-square bg-gray-50 overflow-hidden">
        @if($img)
            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy" decoding="async"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex flex-col gap-1.5">
            @if($discount)
                <span class="px-2.5 py-1 bg-red-500 text-white text-[10px] font-bold rounded-full tracking-wide uppercase shadow-sm">-{{ $discount }}%</span>
            @endif
            @if($product->is_featured)
                <span class="px-2.5 py-1 bg-amber-500 text-white text-[10px] font-bold rounded-full tracking-wide uppercase shadow-sm">{{ __('Featured') }}</span>
            @endif
        </div>

        {{-- Wishlist --}}
        <button class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-white hover:scale-110 hover:text-red-500 text-gray-400" title="{{ __('Add to Wishlist') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>

        {{-- Quick Actions --}}
        <div class="absolute bottom-3 left-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 translate-y-3 group-hover:translate-y-0 transition-all duration-300">
            <button @click="$store.cart.addItem({{ $product->id }})"
                    class="flex-1 py-2.5 bg-emerald-600 text-white text-[11px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                {{ __('Add to Cart') }}
            </button>
            <a href="{{ route('product.show', $product->slug) }}" class="w-10 h-10 bg-white text-gray-600 rounded-xl flex items-center justify-center shadow-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </a>
        </div>
    </div>

    {{-- Info --}}
    <div class="p-4">
        @if($brandName)
            <p class="text-[10px] font-semibold text-indigo-500 uppercase tracking-wider mb-1">{{ $brandName }}</p>
        @endif
        <a href="{{ route('product.show', $product->slug) }}" class="block">
            <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors min-h-[2.2rem]">{{ $name }}</h3>
        </a>

        @if($showSeller && $product->seller)
            <p class="text-[10px] text-gray-400 mt-1">{{ $product->seller->store_name }}</p>
        @endif

        {{-- Rating --}}
        @if($rating > 0)
        <div class="flex items-center gap-1 mt-2">
            <div class="flex items-center gap-0.5">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="w-3 h-3 {{ $i <= round($rating) ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                @endfor
            </div>
            <span class="text-[10px] text-gray-400 font-medium">({{ number_format($rating, 1) }})</span>
            @if($reviewCount > 0)
                <span class="text-[10px] text-gray-400">· {{ $reviewCount }}</span>
            @endif
        </div>
        @endif

        {{-- Price --}}
        <div class="flex items-center justify-between mt-3">
            <div class="flex items-baseline gap-2">
                <span class="text-[17px] font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                @if($discount)
                    <span class="text-[12px] text-gray-400 line-through">${{ number_format($product->compare_at_price, 2) }}</span>
                @endif
            </div>
            @if($inStock)
                <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ __('In Stock') }}</span>
            @else
                <span class="text-[10px] font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">{{ __('Sold Out') }}</span>
            @endif
        </div>
    </div>
</div>
