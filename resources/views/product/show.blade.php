<x-storefront.layout :title="$name . ' - ' . config('app.name')">

    {{-- ═══════════════════════ STRUCTURED DATA ═══════════════════════ --}}
    @push('scripts')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Product",
        "name": {{ json_encode($name) }},
        "description": {{ json_encode($description ?? $name) }},
        "image": {{ json_encode($product->primary_image ? asset('storage/' . $product->primary_image) : null) }},
        "sku": {{ json_encode($product->sku) }},
        "brand": { "@type": "Brand", "name": {{ json_encode($brandName ?? '') }} },
        "offers": {
            "@type": "Offer",
            "priceCurrency": "USD",
            "price": {{ json_encode(number_format($product->price, 2, '.', '')) }},
            "availability": {{ json_encode($product->inStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock') }},
            "seller": { "@type": "Organization", "name": {{ json_encode($product->seller->store_name ?? '') }} }
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": {{ json_encode($averageRating) }},
            "reviewCount": {{ json_encode($totalReviews) }}
        }
    }
    </script>
    @endpush

    {{-- ═══════════════════════ BREADCRUMBS ═══════════════════════ --}}
    <section class="bg-white border-b border-gray-100" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-[12px] text-gray-400 overflow-x-auto scrollbar-none">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors whitespace-nowrap">{{ __('Home') }}</a>
                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                @if($breadcrumbs->count())
                    @foreach($breadcrumbs as $crumb)
                        @if($loop->last)
                            <span class="text-gray-900 font-medium whitespace-nowrap">{{ $locale === 'bn' && $crumb->name_bn ? $crumb->name_bn : $crumb->name }}</span>
                        @else
                            <a href="{{ route('category.show', $crumb->slug) }}" class="hover:text-emerald-600 transition-colors whitespace-nowrap">{{ $locale === 'bn' && $crumb->name_bn ? $crumb->name_bn : $crumb->name }}</a>
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        @endif
                    @endforeach
                @else
                    <span class="text-gray-900 font-medium whitespace-nowrap">{{ $name }}</span>
                @endif
            </nav>
        </div>
    </section>

    {{-- ═══════════════════════ MAIN PRODUCT SECTION ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-6 lg:py-10" x-data="productDetail()" x-init="init()">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

            {{-- ═══════════ IMAGE GALLERY ═══════════ --}}
            <div class="space-y-4" data-gsap="section">
                {{-- Main Image --}}
                <div class="relative bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm aspect-square group cursor-zoom-in"
                     @click="openLightbox()">
                    <template x-if="currentImage">
                        <img :src="currentImage" :alt="{{ json_encode($name) }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="eager" decoding="async" id="mainProductImage">
                    </template>
                    @if(!$product->primary_image)
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Discount Badge --}}
                    @if($product->discount_percentage)
                        <div class="absolute top-4 left-4 z-10">
                            <span class="px-3 py-1.5 bg-red-500 text-white text-[11px] font-bold rounded-full shadow-lg">-{{ $product->discount_percentage }}%</span>
                        </div>
                    @endif

                    {{-- Featured Badge --}}
                    @if($product->is_featured)
                        <div class="absolute top-4 left-4 z-10" :class="{{ $product->discount_percentage ? 'mt-10' : '' }}">
                            <span class="px-3 py-1.5 bg-amber-500 text-white text-[11px] font-bold rounded-full shadow-lg">{{ __('Featured') }}</span>
                        </div>
                    @endif

                    {{-- Zoom Icon --}}
                    <div class="absolute bottom-4 right-4 z-10 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                    </div>
                </div>

                {{-- Thumbnails --}}
                @if($product->images->count() > 1)
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                        @foreach($product->images as $image)
                            <button @click="setImage('{{ asset('storage/' . $image->path) }}', {{ $loop->index }})"
                                    :class="currentImageIndex === {{ $loop->index }} ? 'ring-2 ring-emerald-500 ring-offset-2' : 'ring-1 ring-gray-200 hover:ring-gray-300'"
                                    class="flex-shrink-0 w-16 h-16 lg:w-20 lg:h-20 rounded-xl overflow-hidden transition-all duration-200">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->alt_text ?? $name }}"
                                     class="w-full h-full object-cover" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ═══════════ PRODUCT INFO ═══════════ --}}
            <div class="space-y-6" data-gsap="section">

                {{-- Title & Brand --}}
                <div>
                    @if($brandName)
                        <a href="{{ route('search', ['brand_id' => $product->brand_id]) }}" class="text-[11px] font-bold text-indigo-500 uppercase tracking-widest hover:text-indigo-600 transition-colors">{{ $brandName }}</a>
                    @endif
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1 leading-tight">{{ $name }}</h1>
                    @if($locale === 'bn' && $product->name_bn)
                        <p class="text-[13px] text-gray-500 mt-1">{{ $product->name }}</p>
                    @endif
                </div>

                {{-- Rating & Reviews --}}
                <div class="flex items-center flex-wrap gap-3">
                    @if($totalReviews > 0)
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <span class="text-[13px] font-semibold text-gray-900">{{ number_format($averageRating, 1) }}</span>
                            <span class="text-[12px] text-gray-400">({{ $totalReviews }} {{ __('reviews') }})</span>
                        </div>
                    @else
                        <span class="text-[12px] text-gray-400">{{ __('No reviews yet') }}</span>
                    @endif
                    <span class="text-gray-300">|</span>
                    <span class="text-[12px] text-gray-500">{{ __('SKU') }}: {{ $product->sku }}</span>
                </div>

                {{-- Price --}}
                <div class="bg-gray-50 rounded-2xl p-5">
                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl lg:text-4xl font-bold text-gray-900" x-text="'$' + selectedPrice.toFixed(2)">${{ number_format($product->price, 2) }}</span>
                        @if($product->compare_at_price)
                            <span class="text-lg text-gray-400 line-through" x-show="!selectedComparePrice || selectedComparePrice == selectedPrice">${{ number_format($product->compare_at_price, 2) }}</span>
                            <span class="text-[13px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full" x-show="selectedComparePrice && selectedComparePrice > selectedPrice">
                                -<span x-text="Math.round((1 - selectedPrice / selectedComparePrice) * 100)"></span>%
                            </span>
                        @endif
                    </div>
                    @if($product->compare_at_price && $product->compare_at_price > $product->price)
                        <p class="text-[12px] text-emerald-600 mt-2 font-medium">{{ __('You save') }} $<span x-text="(selectedComparePrice || {{ $product->compare_at_price }} - selectedPrice).toFixed(2)"></span></p>
                    @endif
                </div>

                {{-- Stock Status --}}
                <div class="flex items-center gap-2" x-data="{ stock: {{ $product->available_stock }} }">
                    @if($product->inStock())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[12px] font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            {{ __('In Stock') }}
                            @if($product->manage_stock)
                                <span class="text-emerald-600">({{ $product->quantity }} {{ __('available') }})</span>
                            @endif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 text-[12px] font-semibold rounded-full">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            {{ __('Out of Stock') }}
                        </span>
                    @endif
                    @if($product->isLowStock())
                        <span class="text-[11px] text-amber-600 font-medium">{{ __('Low stock - order soon!') }}</span>
                    @endif
                </div>

                {{-- Variants --}}
                @if($variantAttributes->count())
                    <div class="space-y-4">
                        @foreach($variantAttributes as $variant)
                            @php
                                $attr = $variant['attribute'];
                                $locale = app()->getLocale();
                                $attrName = $locale === 'bn' && $attr->name_bn ? $attr->name_bn : $attr->name;
                            @endphp
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ $attrName }}</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($variant['values'] as $val)
                                        @php
                                            $valName = $locale === 'bn' && $val->value_bn ? $val->value_bn : $val->value;
                                        @endphp
                                        <button @click="selectVariant({{ $attr->id }}, {{ $val->id }}, '{{ addslashes($valName) }}', '{{ $val->color_code ?? '' }}')"
                                                :class="selectedVariants[{{ $attr->id }}] === {{ $val->id }}
                                                    ? 'ring-2 ring-emerald-500 ring-offset-2 bg-emerald-50 border-emerald-300 text-emerald-700'
                                                    : 'ring-1 ring-gray-200 hover:ring-gray-300 bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                                                class="px-4 py-2.5 rounded-xl border text-[13px] font-medium transition-all duration-200 flex items-center gap-2">
                                            @if($val->color_code)
                                                <span class="w-4 h-4 rounded-full border border-gray-200 shadow-inner" style="background-color: {{ $val->color_code }}"></span>
                                            @endif
                                            {{ $valName }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Quantity --}}
                <div>
                    <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Quantity') }}</label>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center bg-gray-100 rounded-xl overflow-hidden">
                            <button @click="quantity = Math.max(1, quantity - 1)" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                            </button>
                            <input type="number" x-model.number="quantity" min="1" max="{{ $product->available_stock }}"
                                   class="w-14 h-10 text-center text-[14px] font-semibold text-gray-900 bg-transparent border-0 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <button @click="quantity = Math.min({{ $product->available_stock }}, quantity + 1)" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        @if($product->manage_stock)
                            <span class="text-[12px] text-gray-400">{{ $product->available_stock }} {{ __('pieces available') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3">
                    <button class="flex-1 py-3.5 bg-emerald-600 text-white text-[14px] font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0"
                            @click="addToCart()">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        {{ __('Add to Cart') }}
                    </button>
                    <button class="flex-1 py-3.5 bg-gray-900 text-white text-[14px] font-semibold rounded-xl hover:bg-gray-800 transition-all shadow-lg flex items-center justify-center gap-2 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0"
                            @click="buyNow()">
                        {{ __('Buy Now') }}
                    </button>
                </div>

                {{-- Wishlist & Share --}}
                <div class="flex items-center gap-3">
                    <button @click="toggleWishlist()" :class="isWishlisted ? 'text-red-500 bg-red-50 border-red-200' : 'text-gray-500 hover:text-red-500 bg-gray-50 border-gray-200 hover:bg-red-50 hover:border-red-200'"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl border text-[13px] font-medium transition-all duration-200">
                        <svg class="w-4 h-4" :fill="isWishlisted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span x-text="isWishlisted ? '{{ __('Saved') }}' : '{{ __('Wishlist') }}'"></span>
                    </button>
                    <div class="relative" x-data="{ shareOpen: false }">
                        <button @click="shareOpen = !shareOpen" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 hover:text-gray-700 hover:bg-gray-100 text-[13px] font-medium transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            {{ __('Share') }}
                        </button>
                        <div x-show="shareOpen" @click.away="shareOpen = false" x-cloak
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 p-2 z-20 min-w-[160px]">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-[13px] text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($name) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-[13px] text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-900" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                Twitter
                            </a>
                            <button @click="navigator.clipboard.writeText('{{ request()->url() }}'); shareOpen = false; $dispatch('show-toast', { message: '{{ __('Link copied!') }}' })" class="w-full flex items-center gap-2 px-3 py-2 text-[13px] text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                {{ __('Copy Link') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Shipping Info --}}
                <div class="bg-gray-50 rounded-2xl p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H18.75m-7.5-3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-gray-900">{{ __('Free Delivery') }}</p>
                            <p class="text-[12px] text-gray-500">{{ __('Free shipping on orders over $50') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-semibold text-gray-900">{{ __('Cash on Delivery') }}</p>
                            <p class="text-[12px] text-gray-500">{{ __('Pay when you receive your order') }}</p>
                        </div>
                    </div>
                    @if($product->weight)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3"/></svg>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-gray-900">{{ __('Weight') }}: {{ $product->weight }}kg</p>
                                @if($product->length && $product->width && $product->height)
                                    <p class="text-[12px] text-gray-500">{{ __('Dimensions') }}: {{ $product->length }}×{{ $product->width }}×{{ $product->height }}cm</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Seller Info --}}
                @if($product->seller)
                    <div class="bg-gray-50 rounded-2xl p-5">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('storefront.show', $product->seller->store_slug) }}">
                                <img src="{{ $product->seller->logo_url }}" alt="{{ $product->seller->store_name }}"
                                     class="w-14 h-14 rounded-xl object-cover ring-2 ring-white shadow-sm">
                            </a>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('storefront.show', $product->seller->store_slug) }}" class="text-[14px] font-semibold text-gray-900 hover:text-emerald-600 transition-colors truncate">{{ $product->seller->store_name }}</a>
                                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    @if($product->seller->average_rating > 0)
                                        <span class="flex items-center gap-1 text-[11px] text-gray-500">
                                            <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            {{ number_format($product->seller->average_rating, 1) }}
                                        </span>
                                    @endif
                                    @if($product->seller->total_sales > 0)
                                        <span class="text-[11px] text-gray-400">{{ number_format($product->seller->total_sales) }} {{ __('sales') }}</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('storefront.show', $product->seller->store_slug) }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-[12px] font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all flex-shrink-0">
                                {{ __('View Store') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ PRODUCT DETAILS TABS ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12" x-data="{ activeTab: 'description' }" data-gsap="section">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Tab Headers --}}
            <div class="flex border-b border-gray-100 overflow-x-auto scrollbar-none">
                <button @click="activeTab = 'description'" :class="activeTab === 'description' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="px-6 py-4 text-[13px] font-semibold whitespace-nowrap border-b-2 transition-all">{{ __('Description') }}</button>
                <button @click="activeTab = 'specs'" :class="activeTab === 'specs' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="px-6 py-4 text-[13px] font-semibold whitespace-nowrap border-b-2 transition-all">{{ __('Specifications') }}</button>
                <button @click="activeTab = 'shipping'" :class="activeTab === 'shipping' ? 'text-emerald-600 border-emerald-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="px-6 py-4 text-[13px] font-semibold whitespace-nowrap border-b-2 transition-all">{{ __('Shipping & Returns') }}</button>
            </div>

            {{-- Tab Content --}}
            <div class="p-6 lg:p-8">
                {{-- Description --}}
                <div x-show="activeTab === 'description'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @if($description)
                        <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">{!! nl2br(e($description)) !!}</div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-[13px] text-gray-400">{{ __('No description available') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Specifications --}}
                <div x-show="activeTab === 'specs'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @php
                        $specAttrs = $product->attributeValues->filter(fn ($av) => !$av->attribute?->is_variant);
                    @endphp
                    @if($specAttrs->count())
                        <div class="space-y-0">
                            @foreach($specAttrs as $i => $attrValue)
                                @php
                                    $attrName = $locale === 'bn' && $attrValue->attribute?->name_bn ? $attrValue->attribute->name_bn : $attrValue->attribute?->name;
                                    $valDisplay = $locale === 'bn' && $attrValue->value_bn ? $attrValue->value_bn : $attrValue->value;
                                @endphp
                                <div class="{{ $i % 2 === 0 ? 'bg-gray-50' : 'bg-white' }} px-5 py-3 flex items-center gap-4 rounded-lg {{ $i === 0 ? 'rounded-t-lg' : '' }} {{ $i === $specAttrs->count() - 1 ? 'rounded-b-lg' : '' }}">
                                    <span class="text-[12px] text-gray-500 w-40 lg:w-56 flex-shrink-0">{{ $attrName }}</span>
                                    <span class="text-[13px] font-medium text-gray-900">{{ $valDisplay }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <p class="text-[13px] text-gray-400">{{ __('No specifications available') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Shipping & Returns --}}
                <div x-show="activeTab === 'shipping'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-6 max-w-2xl">
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900 mb-2">{{ __('Shipping Policy') }}</h3>
                            @if($product->seller?->shipping_policy)
                                <div class="text-[13px] text-gray-600 leading-relaxed">{{ nl2br(e($product->seller->shipping_policy)) }}</div>
                            @else
                                <p class="text-[13px] text-gray-500">{{ __('Free standard shipping on orders over $50. Express shipping available at checkout.') }}</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-[14px] font-semibold text-gray-900 mb-2">{{ __('Return Policy') }}</h3>
                            @if($product->seller?->return_policy)
                                <div class="text-[13px] text-gray-600 leading-relaxed">{{ nl2br(e($product->seller->return_policy)) }}</div>
                            @else
                                <p class="text-[13px] text-gray-500">{{ __('30-day return policy. Items must be unused and in original packaging.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ REVIEWS SECTION ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12" data-gsap="section">
        <x-storefront.section-header
            :title="__('Customer Reviews')"
            :subtitle="__('What people say')"
            accent="amber"
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Rating Summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="text-center mb-6">
                    <div class="text-5xl font-bold text-gray-900">{{ $averageRating }}</div>
                    <div class="flex items-center justify-center gap-0.5 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-[12px] text-gray-400 mt-1">{{ $totalReviews }} {{ __('reviews') }}</p>
                </div>

                {{-- Rating Distribution --}}
                <div class="space-y-2">
                    @for($i = 5; $i >= 1; $i--)
                        @php
                            $count = $ratingDistribution[$i] ?? 0;
                            $percent = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="text-[12px] text-gray-500 w-4 text-right">{{ $i }}</span>
                            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-[11px] text-gray-400 w-8 text-right">{{ $count }}</span>
                        </div>
                    @endfor
                </div>

                {{-- Write Review Button --}}
                @auth
                    <button @click="$refs.reviewForm.scrollIntoView({ behavior: 'smooth' })"
                            class="w-full mt-6 py-3 bg-amber-500 text-white text-[13px] font-semibold rounded-xl hover:bg-amber-600 transition-colors shadow-lg shadow-amber-500/20">
                        {{ __('Write a Review') }}
                    </button>
                @else
                    <a href="{{ route('login') }}" class="block w-full mt-6 py-3 bg-gray-100 text-gray-700 text-[13px] font-semibold rounded-xl hover:bg-gray-200 transition-colors text-center">
                        {{ __('Login to Review') }}
                    </a>
                @endauth
            </div>

            {{-- Reviews List --}}
            <div class="lg:col-span-2 space-y-4">
                @if($reviews->count())
                    @foreach($reviews as $review)
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white text-[13px] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-[13px] font-semibold text-gray-900">{{ $review->user->name }}</span>
                                        @if($review->is_verified_purchase)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                {{ __('Verified Purchase') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="flex items-center gap-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        <span class="text-[11px] text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($review->title)
                                        <h4 class="text-[13px] font-semibold text-gray-900 mt-2">{{ $review->title }}</h4>
                                    @endif
                                    @if($review->comment)
                                        <p class="text-[13px] text-gray-600 mt-1 leading-relaxed">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-6">
                        {{ $reviews->links('components.storefront.pagination') }}
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No reviews yet') }}</h3>
                        <p class="text-[13px] text-gray-500">{{ __('Be the first to share your experience with this product.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Write Review Form --}}
        @auth
            <div x-ref="reviewForm" class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <h3 class="text-[16px] font-bold text-gray-900 mb-4">{{ __('Write Your Review') }}</h3>
                <form method="POST" action="{{ route('reviews.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="space-y-4">
                        {{-- Rating --}}
                        <div x-data="{ rating: 0, hover: 0 }">
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Your Rating') }} *</label>
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" @click="rating = {{ $i }}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0"
                                            class="focus:outline-none transition-transform hover:scale-110">
                                        <svg class="w-8 h-8" :class="(hover >= {{ $i }} || rating >= {{ $i }}) ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    </button>
                                @endfor
                                <input type="hidden" name="rating" :value="rating" required>
                            </div>
                        </div>

                        {{-- Title --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Review Title') }}</label>
                            <input type="text" name="title" placeholder="{{ __('Summarize your experience') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                        </div>

                        {{-- Comment --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Your Review') }}</label>
                            <textarea name="comment" rows="4" placeholder="{{ __('Tell others about your experience with this product...') }}"
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors resize-none"></textarea>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-amber-500 text-white text-[13px] font-semibold rounded-xl hover:bg-amber-600 transition-colors shadow-lg shadow-amber-500/20">
                            {{ __('Submit Review') }}
                        </button>
                    </div>
                </form>
            </div>
        @endauth
    </section>

    {{-- ═══════════════════════ RELATED PRODUCTS ═══════════════════════ --}}
    @if($relatedProducts->count())
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12" data-gsap="section">
        <x-storefront.section-header
            :title="__('Related')"
            :subtitle="__('You May Also Like')"
            accent="emerald"
            :viewAllHref="route('search', ['category_id' => $product->category_id])"
            :viewAllText="__('View All')"
        />
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($relatedProducts->take(4) as $related)
                <x-storefront.product-card :product="$related" />
            @endforeach
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ RECOMMENDED PRODUCTS ═══════════════════════ --}}
    @if($recommendedProducts->count())
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12 bg-white" data-gsap="section">
        <x-storefront.section-header
            :title="__('Recommended')"
            :subtitle="__('From the Same Brand')"
            accent="indigo"
        />
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($recommendedProducts->take(4) as $rec)
                <x-storefront.product-card :product="$rec" />
            @endforeach
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ RECENTLY VIEWED ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12" data-gsap="section" x-data="recentlyViewed()" x-init="init()">
        <x-storefront.section-header
            :title="__('Continue Shopping')"
            :subtitle="__('Recently Viewed')"
            accent="gray"
        />
        <div x-show="products.length > 0" x-transition>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="p in products" :key="p.slug">
                    <a :href="'/product/' + p.slug" class="group relative bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-500 hover:-translate-y-1">
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            <img :src="p.image" :alt="p.name" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                        </div>
                        <div class="p-4">
                            <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors" x-text="p.name"></h3>
                            <span class="text-[17px] font-bold text-gray-900 mt-2 block" x-text="'$' + parseFloat(p.price).toFixed(2)"></span>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ LIGHTBOX ═══════════════════════ --}}
    <div x-show="lightboxOpen" x-cloak
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
         @click="lightboxOpen = false" @keydown.escape.window="lightboxOpen = false">
        <button @click="lightboxOpen = false" class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors z-10">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <button @click.stop="prevImage()" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors z-10">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click.stop="nextImage()" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors z-10">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <img :src="currentImage" :alt="{{ json_encode($name) }}" class="max-w-full max-h-[85vh] object-contain rounded-lg" @click.stop>
    </div>

    {{-- ═══════════════════════ SCRIPTS ═══════════════════════ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script>
        function productDetail() {
            return {
                currentImage: @json($product->primary_image ? asset('storage/' . $product->primary_image) : null),
                currentImageIndex: 0,
                allImages: @json($product->images->map(fn ($img) => asset('storage/' . $img->path))),
                quantity: 1,
                isWishlisted: false,
                lightboxOpen: false,
                selectedVariants: {},
                selectedVariantNames: {},
                selectedPrice: {{ $product->price }},
                selectedComparePrice: {{ $product->compare_at_price ?? 'null' }},
                selectedSku: @json($product->sku),
                init() {
                    this.allImages.forEach((img, i) => {
                        if (img === this.currentImage) this.currentImageIndex = i;
                    });
                    this.checkWishlist();
                    this.trackRecentlyViewed();
                },
                async checkWishlist() {
                    try {
                        const res = await fetch('/wishlist/check/{{ $product->id }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) this.isWishlisted = data.is_wishlisted;
                    } catch (e) {}
                },
                trackRecentlyViewed() {
                    try {
                        fetch('/recently-viewed/track/{{ $product->id }}', {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).catch(() => {});
                        const stored = JSON.parse(localStorage.getItem('recently_viewed') || '[]');
                        const current = {
                            slug: @json($product->slug),
                            name: @json($name),
                            price: {{ $product->price }},
                            image: @json($product->primary_image ? asset('storage/' . $product->primary_image) : null)
                        };
                        const updated = [current, ...stored.filter(p => p.slug !== '{{ $product->slug }}')].slice(0, 20);
                        localStorage.setItem('recently_viewed', JSON.stringify(updated));
                    } catch (e) {}
                },
                setImage(src, index) {
                    this.currentImage = src;
                    this.currentImageIndex = index;
                    if (typeof gsap !== 'undefined') {
                        gsap.fromTo('#mainProductImage', { opacity: 0, scale: 0.95 }, { opacity: 1, scale: 1, duration: 0.4, ease: 'power2.out' });
                    }
                },
                nextImage() {
                    if (this.allImages.length === 0) return;
                    const next = (this.currentImageIndex + 1) % this.allImages.length;
                    this.setImage(this.allImages[next], next);
                },
                prevImage() {
                    if (this.allImages.length === 0) return;
                    const prev = (this.currentImageIndex - 1 + this.allImages.length) % this.allImages.length;
                    this.setImage(this.allImages[prev], prev);
                },
                openLightbox() {
                    if (this.allImages.length > 0) this.lightboxOpen = true;
                },
                selectVariant(attrId, valId, valName, colorCode) {
                    if (this.selectedVariants[attrId] === valId) {
                        delete this.selectedVariants[attrId];
                        delete this.selectedVariantNames[attrId];
                    } else {
                        this.selectedVariants[attrId] = valId;
                        this.selectedVariantNames[attrId] = valName;
                    }
                },
                toggleWishlist() {
                    this.isWishlisted = !this.isWishlisted;
                    fetch('/wishlist/toggle/{{ $product->id }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json()).then(d => {
                        if (d.success) {
                            this.isWishlisted = d.action === 'added';
                            this.$dispatch('show-toast', { message: d.message });
                            const badge = document.querySelector('[x-ref="wishlistBadge"]');
                            if (badge) badge.textContent = d.wishlist_count;
                        } else {
                            this.isWishlisted = !this.isWishlisted;
                            this.$dispatch('show-toast', { message: d.message });
                        }
                    }).catch(() => {
                        this.isWishlisted = !this.isWishlisted;
                    });
                },
                addToCart() {
                    const variants = Object.keys(this.selectedVariants).length > 0 ? this.selectedVariants : null;
                    Alpine.store('cart').addItem({{ $product->id }}, this.quantity, variants);
                },
                async buyNow() {
                    const variants = Object.keys(this.selectedVariants).length > 0 ? this.selectedVariants : null;
                    await Alpine.store('cart').addItem({{ $product->id }}, this.quantity, variants);
                    window.location.href = '/checkout';
                }
            };
        }

        function recentlyViewed() {
            return {
                products: [],
                init() {
                    @auth
                        fetch('/recently-viewed', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(d => {
                                if (d.success) {
                                    this.products = d.items.filter(p => p.slug !== '{{ $product->slug }}').slice(0, 4);
                                }
                            })
                            .catch(() => {});
                    @else
                        try {
                            const stored = localStorage.getItem('recently_viewed');
                            const all = stored ? JSON.parse(stored) : [];
                            this.products = all.filter(p => p.slug !== '{{ $product->slug }}').slice(0, 4);
                        } catch (e) { this.products = []; }
                    @endauth
                }
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
            gsap.registerPlugin(ScrollTrigger);

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) return;

            gsap.utils.toArray('[data-gsap="section"]').forEach(el => {
                gsap.fromTo(el,
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out',
                      scrollTrigger: { trigger: el, start: 'top 88%', once: true } }
                );
            });

            gsap.fromTo('.product-card',
                { opacity: 0, y: 30 },
                { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out',
                  scrollTrigger: { trigger: '.product-card', start: 'top 92%', once: true } }
            );
        });
    </script>
    @endpush

</x-storefront.layout>
