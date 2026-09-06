@props(['category'])

@php
    $img = $category->image ? asset('storage/' . $category->image) : null;
    $locale = app()->getLocale();
    $name = $locale === 'bn' && $category->name_bn ? $category->name_bn : $category->name;
@endphp

<a href="{{ route('search', ['category_id' => $category->id]) }}" class="category-card group relative block bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-500 hover:-translate-y-1" data-gsap="category">
    <div class="aspect-[4/3] bg-gradient-to-br from-gray-50 to-gray-100 relative overflow-hidden">
        @if($img)
            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy" decoding="async"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-14 h-14 text-gray-300 group-hover:text-emerald-400 transition-colors duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
    </div>
    <div class="p-4 text-center">
        <h3 class="text-[13px] font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $name }}</h3>
        @if($category->products_count !== null)
            <p class="text-[11px] text-gray-400 mt-0.5">{{ $category->products_count }} {{ __('products') }}</p>
        @endif
    </div>
</a>
