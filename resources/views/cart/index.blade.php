<x-storefront.layout title="{{ __('Shopping Cart') }}">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <nav class="flex items-center gap-2 text-[12px] text-gray-400">
                <a href="/" class="hover:text-emerald-600 transition-colors">{{ __('Home') }}</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-medium">{{ __('Shopping Cart') }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[28px] font-bold text-gray-900">{{ __('Shopping Cart') }}</h1>
                <p class="text-[13px] text-gray-400 mt-1" x-text="$store.cart.count + ' {{ __('item(s) in your cart') }}'"></p>
            </div>
            <template x-if="$store.cart.count > 0">
                <button @click="$store.cart.clearCart()" class="text-[12px] font-medium text-red-500 hover:text-red-600 transition-colors">
                    {{ __('Clear Cart') }}
                </button>
            </template>
        </div>

        <template x-if="$store.cart.count === 0">
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <h2 class="text-[20px] font-bold text-gray-900 mb-2">{{ __('Your cart is empty') }}</h2>
                <p class="text-[14px] text-gray-400 mb-6 text-center max-w-md">{{ __('Looks like you haven\'t added anything to your cart yet. Browse our products and find something you love!') }}</p>
                <a href="/" class="px-8 py-3 bg-emerald-600 text-white text-[14px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20">
                    {{ __('Start Shopping') }}
                </a>
            </div>
        </template>

        <template x-if="$store.cart.count > 0">
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Cart Items --}}
                <div class="flex-1 space-y-4">
                    <template x-for="item in $store.cart.items" :key="item.id">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg hover:shadow-gray-200/50 transition-all duration-300">
                            <div class="flex gap-5">
                                {{-- Image --}}
                                <div class="w-28 h-28 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0 border border-gray-100">
                                    <template x-if="item.image">
                                        <a :href="'/product/' + item.slug">
                                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                        </a>
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <a :href="'/product/' + item.slug" class="block">
                                                <h3 class="text-[15px] font-semibold text-gray-900 hover:text-emerald-600 transition-colors line-clamp-2" x-text="item.name"></h3>
                                            </a>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <span class="text-[11px] text-gray-400">{{ __('Seller') }}:</span>
                                                <span class="text-[11px] font-medium text-gray-600" x-text="item.seller"></span>
                                            </div>
                                            <template x-if="!item.in_stock">
                                                <p class="text-[11px] text-red-500 font-medium mt-1">{{ __('Out of stock') }}</p>
                                            </template>
                                        </div>
                                        <button @click="$store.cart.removeItem(item.id)"
                                                class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex items-end justify-between mt-4">
                                        {{-- Quantity --}}
                                        <div class="flex items-center bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                                            <button @click="$store.cart.updateQuantity(item.id, item.quantity - 1)"
                                                    :disabled="$store.cart.loading"
                                                    class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white transition-colors disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                                </svg>
                                            </button>
                                            <span class="w-12 h-10 flex items-center justify-center text-[15px] font-semibold text-gray-900 border-x border-gray-200"
                                                  x-text="item.quantity"></span>
                                            <button @click="$store.cart.updateQuantity(item.id, item.quantity + 1)"
                                                    :disabled="$store.cart.loading || item.quantity >= item.max_stock"
                                                    class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white transition-colors disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Price --}}
                                        <div class="text-right">
                                            <p class="text-[20px] font-bold text-gray-900" x-text="'$' + (item.price * item.quantity).toFixed(2)"></p>
                                            <template x-if="item.quantity > 1">
                                                <p class="text-[11px] text-gray-400" x-text="'$' + item.price.toFixed(2) + ' {{ __('each') }}'"></p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Order Summary --}}
                <div class="w-full lg:w-[380px] flex-shrink-0">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24 space-y-6">
                        <h2 class="text-[17px] font-bold text-gray-900">{{ __('Order Summary') }}</h2>

                        {{-- Coupon --}}
                        <div class="space-y-2">
                            <label class="text-[13px] font-medium text-gray-700">{{ __('Coupon Code') }}</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="$store.cart.couponCode"
                                       :disabled="$store.cart.couponApplied"
                                       placeholder="{{ __('Enter coupon code') }}"
                                       class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 disabled:opacity-50">
                                <template x-if="!$store.cart.couponApplied">
                                    <button @click="$store.cart.applyCoupon()"
                                            :disabled="$store.cart.loading || !$store.cart.couponCode.trim()"
                                            class="px-5 py-2.5 bg-gray-900 text-white text-[12px] font-semibold rounded-xl hover:bg-gray-800 transition-colors disabled:opacity-50">
                                        {{ __('Apply') }}
                                    </button>
                                </template>
                                <template x-if="$store.cart.couponApplied">
                                    <button @click="$store.cart.removeCoupon()"
                                            class="px-5 py-2.5 bg-red-50 text-red-600 text-[12px] font-semibold rounded-xl hover:bg-red-100 transition-colors">
                                        {{ __('Remove') }}
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[13px] text-gray-500">{{ __('Subtotal') }}</span>
                                <span class="text-[13px] font-semibold text-gray-900" x-text="$store.cart.formattedSubtotal"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[13px] text-gray-500">{{ __('Shipping') }}</span>
                                <span class="text-[13px] font-semibold" :class="$store.cart.shipping === 0 ? 'text-emerald-600' : 'text-gray-900'" x-text="$store.cart.formattedShipping"></span>
                            </div>
                            <template x-if="$store.cart.shipping > 0">
                                <p class="text-[11px] text-emerald-600 bg-emerald-50 rounded-lg px-3 py-1.5">
                                    {{ __('Free shipping on orders over') }} $50.00
                                </p>
                            </template>
                            <template x-if="$store.cart.couponDiscount > 0">
                                <div class="flex items-center justify-between">
                                    <span class="text-[13px] text-gray-500">{{ __('Coupon Discount') }}</span>
                                    <span class="text-[13px] font-semibold text-emerald-600" x-text="$store.cart.formattedCouponDiscount"></span>
                                </div>
                            </template>
                            <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                                <span class="text-[15px] font-bold text-gray-900">{{ __('Total') }}</span>
                                <span class="text-[20px] font-bold text-gray-900" x-text="$store.cart.formattedGrandTotal"></span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="space-y-3 pt-2">
                            <a href="/checkout" class="block w-full py-3.5 bg-emerald-600 text-white text-[14px] font-semibold rounded-xl hover:bg-emerald-700 transition-all text-center shadow-lg shadow-emerald-600/20 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0">
                                {{ __('Proceed to Checkout') }}
                            </a>
                            <a href="/" class="block w-full py-3.5 bg-gray-100 text-gray-700 text-[14px] font-semibold rounded-xl hover:bg-gray-200 transition-colors text-center">
                                {{ __('Continue Shopping') }}
                            </a>
                        </div>

                        {{-- Trust Badges --}}
                        <div class="flex items-center justify-center gap-4 pt-2">
                            <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                {{ __('Secure Checkout') }}
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-gray-400">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                {{ __('Free Returns') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Recommended Products --}}
        @if(isset($recommendedProducts) && $recommendedProducts->count())
        <div class="mt-16">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-[20px] font-bold text-gray-900">{{ __('You May Also Like') }}</h2>
                <a href="/" class="text-[12px] font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                    {{ __('View All') }} →
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($recommendedProducts as $product)
                    @php
                        $locale = app()->getLocale();
                        $name = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
                        $img = $product->primary_image ? asset('storage/' . $product->primary_image) : null;
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1" data-gsap="product">
                        <div class="relative aspect-square bg-gray-50 overflow-hidden">
                            @if($img)
                                <a href="{{ route('product.show', $product->slug) }}">
                                    <img src="{{ $img }}" alt="{{ $name }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500" loading="lazy">
                                </a>
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <a href="{{ route('product.show', $product->slug) }}" class="block">
                                <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2 leading-snug min-h-[2.2rem]">{{ $name }}</h3>
                            </a>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-[16px] font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                <button @click="$store.cart.addItem({{ $product->id }})"
                                        class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center hover:bg-emerald-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    </script>
    @endpush

</x-storefront.layout>
