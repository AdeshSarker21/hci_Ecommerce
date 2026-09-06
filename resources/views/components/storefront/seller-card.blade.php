@props(['seller'])

@php
    $locale = app()->getLocale();
    $name = $locale === 'bn' && $seller->store_name ? $seller->store_name : $seller->store_name;
    $tagline = $locale === 'bn' ? ($seller->store_tagline_bn ?? $seller->store_tagline ?? null) : ($seller->store_tagline ?? null);
@endphp

<a href="{{ route('storefront.show', $seller->store_slug) }}" class="group flex items-center gap-4 p-5 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-white hover:shadow-xl hover:shadow-gray-200/50 hover:-translate-y-0.5 transition-all duration-500" data-gsap="category">
    <div class="relative flex-shrink-0">
        <img src="{{ $seller->logo_url }}" alt="{{ $name }}" class="w-14 h-14 rounded-xl object-cover ring-2 ring-white shadow-sm group-hover:ring-emerald-200 transition-all duration-300">
        @if($seller->is_featured)
            <div class="absolute -top-1 -right-1 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center shadow-sm">
                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="text-[14px] font-semibold text-gray-900 truncate group-hover:text-emerald-600 transition-colors">{{ $name }}</h3>
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        </div>
        @if($tagline)
            <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $tagline }}</p>
        @endif
        <div class="flex items-center gap-3 mt-1">
            @if($seller->average_rating > 0)
                <span class="flex items-center gap-1 text-[11px] text-gray-500">
                    <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    {{ number_format($seller->average_rating, 1) }}
                </span>
            @endif
            <span class="text-[11px] text-gray-400">{{ $seller->products_count }} {{ __('products') }}</span>
            @if($seller->total_sales > 0)
                <span class="text-[11px] text-gray-400">{{ number_format($seller->total_sales) }} {{ __('sales') }}</span>
            @endif
        </div>
    </div>
    <svg class="w-5 h-5 text-gray-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
</a>
