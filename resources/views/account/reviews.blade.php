<x-account.layout :title="__('My Reviews')" active="reviews">
    @php $locale = app()->getLocale(); @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('My Reviews') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('Reviews you have written for products.') }}</p>
        </div>

        {{-- Reviews List --}}
        @if($reviews->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No reviews yet') }}</h3>
                <p class="text-[13px] text-gray-500 mb-5">{{ __('Purchase products and share your experience.') }}</p>
                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    {{ __('Browse Products') }}
                </a>
            </div>
        @else
            <div class="space-y-4 account-list" style="opacity: 0;">
                @foreach($reviews as $review)
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 review-card" data-gsap="review">
                        <div class="flex items-start gap-4">
                            @php
                                $product = $review->product;
                                $name = $locale === 'bn' && $product?->name_bn ? $product->name_bn : ($product?->name ?? __('Unknown Product'));
                                $img = $product?->primary_image ? asset('storage/' . $product->primary_image) : null;
                            @endphp
                            <a href="{{ $product ? route('product.show', $product->slug) : '#' }}" class="flex-shrink-0">
                                <div class="w-14 h-14 bg-gray-100 rounded-xl overflow-hidden">
                                    @if($img)
                                        <img src="{{ $img }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $product ? route('product.show', $product->slug) : '#' }}" class="text-[13px] font-semibold text-gray-900 hover:text-emerald-600 transition-colors line-clamp-1">{{ $name }}</a>
                                <div class="flex items-center gap-1 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endif
                                    @endfor
                                    <span class="text-[11px] text-gray-400 ml-1">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                @if($review->title)
                                    <p class="text-[13px] font-semibold text-gray-900 mt-2">{{ $review->title }}</p>
                                @endif
                                @if($review->comment)
                                    <p class="text-[12px] text-gray-600 mt-1 line-clamp-2">{{ $review->comment }}</p>
                                @endif
                                @if($review->is_verified_purchase)
                                    <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-semibold rounded-full">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ __('Verified Purchase') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.account-header, .account-empty, .account-list, .review-card').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.account-header', '.account-empty', '.account-list'], { y: 20 });
            tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
              .to(['.account-empty', '.account-list'], { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.review-card', { opacity: 1, y: 0, duration: 0.3, stagger: 0.06 }, '-=0.3');
        });
    </script>
    @endpush
</x-account.layout>
