<x-account.layout :title="__('Recently Viewed')" active="recently-viewed">
    @php $locale = app()->getLocale(); @endphp

    <div class="space-y-6">

        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('Recently Viewed') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('Products you recently looked at.') }}</p>
        </div>

        @if($items->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No recently viewed products') }}</h3>
                <p class="text-[13px] text-gray-500 mb-5">{{ __('Browse products and they will appear here.') }}</p>
                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    {{ __('Browse Products') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 account-grid" style="opacity: 0;">
                @foreach($items as $item)
                    @php
                        $product = $item->product;
                        if (!$product) continue;
                        $name = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
                        $img = $product->primary_image ? asset('storage/' . $product->primary_image) : null;
                    @endphp
                    <a href="{{ route('product.show', $product->slug) }}" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 product-card" data-gsap="product">
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-3">
                            <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors">{{ $name }}</h3>
                            <span class="text-[15px] font-bold text-gray-900 mt-2 block">${{ number_format($product->price, 2) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
    </script>
    @endpush
</x-account.layout>
