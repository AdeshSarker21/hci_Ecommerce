<x-storefront.layout title="{{ __('My Wishlist') }}">

    {{-- ═══════════════════════ BREADCRUMBS ═══════════════════════ --}}
    <section class="bg-white border-b border-gray-100" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex items-center gap-2 text-[12px] text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-emerald-600 transition-colors">{{ __('Home') }}</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-medium">{{ __('My Wishlist') }}</span>
            </nav>
        </div>
    </section>

    {{-- ═══════════════════════ WISHLIST CONTENT ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-6 lg:py-10" data-gsap="section"
             x-data="wishlistPage()" x-init="init()">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[22px] font-bold text-gray-900">{{ __('My Wishlist') }}</h1>
                <p class="text-[13px] text-gray-500 mt-1">
                    {{ $wishlistCount }} {{ __('saved products') }}
                </p>
            </div>
            @if($wishlistItems->count() > 0)
                <button @click="clearAll()"
                        class="px-4 py-2 text-[12px] font-medium text-red-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-red-200">
                    {{ __('Clear All') }}
                </button>
            @endif
        </div>

        {{-- Wishlist Grid --}}
        @if($wishlistItems->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($wishlistItems as $wishlistItem)
                    @php
                        $product = $wishlistItem->product;
                        $locale = app()->getLocale();
                        $name = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
                        $img = $product->primary_image ? asset('storage/' . $product->primary_image) : null;
                        $discount = $product->discount_percentage;
                        $inStock = $product->inStock();
                        $brandName = $locale === 'bn' && $product->brand?->name_bn ? $product->brand->name_bn : $product->brand?->name;
                    @endphp
                    <div class="product-card group relative bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-500 hover:-translate-y-1"
                         id="wishlist-card-{{ $product->id }}" data-gsap="product">
                        {{-- Image --}}
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            <a href="{{ route('product.show', $product->slug) }}">
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
                            </a>

                            {{-- Badges --}}
                            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                @if($discount)
                                    <span class="px-2.5 py-1 bg-red-500 text-white text-[10px] font-bold rounded-full tracking-wide uppercase shadow-sm">-{{ $discount }}%</span>
                                @endif
                                @if(!$inStock)
                                    <span class="px-2.5 py-1 bg-gray-900 text-white text-[10px] font-bold rounded-full tracking-wide uppercase shadow-sm">{{ __('Sold Out') }}</span>
                                @endif
                            </div>

                            {{-- Remove Button --}}
                            <button @click="removeItem({{ $product->id }})"
                                    class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm text-red-400 hover:text-red-500 hover:bg-white hover:scale-110 transition-all duration-300"
                                    title="{{ __('Remove from Wishlist') }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>

                            {{-- Move to Cart Button --}}
                            <div class="absolute bottom-3 left-3 right-3 opacity-0 group-hover:opacity-100 translate-y-3 group-hover:translate-y-0 transition-all duration-300">
                                @if($inStock)
                                    <button @click="moveToCart({{ $product->id }})"
                                            class="w-full py-2.5 bg-emerald-600 text-white text-[11px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                        <span>{{ __('Move to Cart') }}</span>
                                    </button>
                                @else
                                    <button disabled class="w-full py-2.5 bg-gray-400 text-white text-[11px] font-semibold rounded-xl cursor-not-allowed flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                        <span>{{ __('Out of Stock') }}</span>
                                    </button>
                                @endif
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
                            @if($product->seller)
                                <p class="text-[10px] text-gray-400 mt-1">{{ $product->seller->store_name }}</p>
                            @endif

                            {{-- Price --}}
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-[17px] font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                    @if($product->compare_at_price && $product->compare_at_price > $product->price)
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
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-16 lg:py-24">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="text-[18px] font-bold text-gray-900 mb-2">{{ __('Your wishlist is empty') }}</h3>
                <p class="text-[13px] text-gray-500 mb-8 max-w-sm mx-auto">{{ __('Save products you love to your wishlist and come back to them anytime.') }}</p>
                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                    {{ __('Start Shopping') }}
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        @endif
    </section>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script>
        function wishlistPage() {
            return {
                init() {
                    this.$nextTick(() => this.animateCards());
                },
                async removeItem(productId) {
                    const card = document.getElementById('wishlist-card-' + productId);
                    if (card && typeof gsap !== 'undefined') {
                        await gsap.to(card, { opacity: 0, scale: 0.8, duration: 0.3, ease: 'power2.in' });
                    }
                    try {
                        const res = await fetch('/wishlist/' + productId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (card) card.remove();
                            this.$dispatch('show-toast', { message: data.message });
                            const countEl = document.querySelector('[x-ref="wishlistBadge"]');
                            if (countEl) countEl.textContent = data.wishlist_count;
                            const countText = document.querySelector('.text-gray-500.mt-1');
                            if (countText) countText.textContent = data.wishlist_count + ' {{ __("saved products") }}';
                            if (data.wishlist_count === 0) location.reload();
                        }
                    } catch (e) {
                        if (card) card.style.opacity = '1';
                        console.error(e);
                    }
                },
                async moveToCart(productId) {
                    try {
                        const res = await fetch('/wishlist/move-to-cart/' + productId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const card = document.getElementById('wishlist-card-' + productId);
                            if (card && typeof gsap !== 'undefined') {
                                await gsap.to(card, { opacity: 0, x: -30, duration: 0.3, ease: 'power2.in' });
                            }
                            if (card) card.remove();
                            this.$dispatch('show-toast', { message: data.message });
                            if (typeof Alpine !== 'undefined' && Alpine.store('cart')) {
                                Alpine.store('cart').fetchSummary();
                            }
                            const countEl = document.querySelector('[x-ref="wishlistBadge"]');
                            if (countEl) countEl.textContent = data.wishlist_count;
                            if (data.wishlist_count === 0) location.reload();
                        } else {
                            this.$dispatch('show-toast', { message: data.message });
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },
                async clearAll() {
                    if (!confirm('{{ __("Are you sure you want to clear your wishlist?") }}')) return;
                    try {
                        const res = await fetch('/wishlist', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.$dispatch('show-toast', { message: data.message });
                            location.reload();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },
                animateCards() {
                    if (typeof gsap === 'undefined') return;
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    if (prefersReducedMotion) return;
                    gsap.utils.toArray('[data-gsap="product"]').forEach((el, i) => {
                        gsap.fromTo(el,
                            { opacity: 0, y: 30 },
                            { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out', delay: i * 0.08 }
                        );
                    });
                    gsap.utils.toArray('[data-gsap="section"]').forEach(el => {
                        gsap.fromTo(el,
                            { opacity: 0, y: 30 },
                            { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out',
                              scrollTrigger: { trigger: el, start: 'top 88%', once: true } }
                        );
                    });
                }
            };
        }
    </script>
    @endpush

</x-storefront.layout>
