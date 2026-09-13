<x-account.layout :title="__('Wishlist')" active="wishlist">
    @php $locale = app()->getLocale(); @endphp

    <div class="space-y-6" x-data="wishlistPage()">

        {{-- Header --}}
        <div class="flex items-center justify-between account-header" style="opacity: 0;">
            <div>
                <h1 class="text-[22px] font-bold text-gray-900">{{ __('My Wishlist') }}</h1>
                <p class="text-[13px] text-gray-500 mt-1">{{ $wishlistCount }} {{ __('saved products') }}</p>
            </div>
            @if($wishlistItems->count() > 0)
                <button @click="clearAll()" class="px-4 py-2 text-[12px] font-medium text-red-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors border border-red-200">
                    {{ __('Clear All') }}
                </button>
            @endif
        </div>

        {{-- Wishlist Grid --}}
        @if($wishlistItems->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 account-grid" style="opacity: 0;">
                @foreach($wishlistItems as $wishlistItem)
                    @php
                        $product = $wishlistItem->product;
                        $name = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
                        $img = $product->primary_image ? asset('storage/' . $product->primary_image) : null;
                        $inStock = $product->quantity > 0;
                    @endphp
                    <div class="group relative bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 product-card" id="wishlist-card-{{ $product->id }}" data-gsap="product">
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            <a href="{{ route('product.show', $product->slug) }}">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </a>
                            <button @click="removeItem({{ $product->id }})"
                                    class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm text-red-400 hover:text-red-500 hover:bg-white hover:scale-110 transition-all duration-300"
                                    title="{{ __('Remove from Wishlist') }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </button>
                            <div class="absolute bottom-3 left-3 right-3 opacity-0 group-hover:opacity-100 translate-y-3 group-hover:translate-y-0 transition-all duration-300">
                                @if($inStock)
                                    <button @click="moveToCart({{ $product->id }})" class="w-full py-2.5 bg-emerald-600 text-white text-[11px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                        {{ __('Move to Cart') }}
                                    </button>
                                @else
                                    <button disabled class="w-full py-2.5 bg-gray-400 text-white text-[11px] font-semibold rounded-xl cursor-not-allowed flex items-center justify-center gap-1.5">
                                        {{ __('Out of Stock') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="p-3">
                            <a href="{{ route('product.show', $product->slug) }}" class="block">
                                <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors min-h-[2.2rem]">{{ $name }}</h3>
                            </a>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-[15px] font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('Your wishlist is empty') }}</h3>
                <p class="text-[13px] text-gray-500 mb-5">{{ __('Save products you love and come back to them anytime.') }}</p>
                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    {{ __('Start Shopping') }}
                </a>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        function wishlistPage() {
            return {
                async removeItem(productId) {
                    const card = document.getElementById('wishlist-card-' + productId);
                    if (card && typeof gsap !== 'undefined') {
                        await gsap.to(card, { opacity: 0, scale: 0.8, duration: 0.3, ease: 'power2.in' });
                    }
                    try {
                        const res = await fetch('/wishlist/' + productId, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) location.reload();
                    } catch (e) { console.error(e); }
                },
                async moveToCart(productId) {
                    try {
                        const res = await fetch('/wishlist/move-to-cart/' + productId, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) location.reload();
                    } catch (e) { console.error(e); }
                },
                async clearAll() {
                    if (!confirm('{{ __("Are you sure you want to clear your wishlist?") }}')) return;
                    try {
                        const res = await fetch('/wishlist', {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) location.reload();
                    } catch (e) { console.error(e); }
                },
                init() {
                    this.$nextTick(() => {
                        if (typeof gsap === 'undefined') {
                            document.querySelectorAll('.account-header, .account-empty, .account-grid, .product-card').forEach(el => el.style.opacity = '1');
                            return;
                        }
                        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
                        gsap.set(['.account-header', '.account-empty', '.account-grid'], { y: 20 });
                        tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
                          .to(['.account-empty', '.account-grid'], { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
                          .to('.product-card', { opacity: 1, y: 0, duration: 0.3, stagger: 0.06 }, '-=0.3');
                    });
                }
            };
        }
    </script>
    @endpush
</x-account.layout>
