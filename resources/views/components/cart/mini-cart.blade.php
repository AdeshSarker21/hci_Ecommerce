{{-- Mini Cart Drawer --}}
<div x-data x-show="$store.cart.open" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="$store.cart.open = false"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9998]"
     style="display: none;">

    {{-- Drawer --}}
    <div @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-gray-900">{{ __('Shopping Cart') }}</h3>
                    <p class="text-[11px] text-gray-400" x-text="$store.cart.count + ' {{ __('item(s)') }}'"></p>
                </div>
            </div>
            <button @click="$store.cart.open = false"
                    class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Items --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
            <template x-if="$store.cart.count === 0">
                <div class="flex flex-col items-center justify-center h-full text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                    </div>
                    <p class="text-[14px] font-semibold text-gray-900 mb-1">{{ __('Your cart is empty') }}</p>
                    <p class="text-[12px] text-gray-400 mb-4">{{ __('Add items to get started') }}</p>
                    <button @click="$store.cart.open = false"
                            class="px-5 py-2.5 bg-emerald-600 text-white text-[12px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                        {{ __('Continue Shopping') }}
                    </button>
                </div>
            </template>

            <template x-for="item in $store.cart.items" :key="item.id">
                <div class="flex gap-4 p-3 bg-gray-50 rounded-2xl group hover:bg-gray-100 transition-colors">
                    {{-- Image --}}
                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white flex-shrink-0 border border-gray-100">
                        <template x-if="item.image">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!item.image">
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-[13px] font-semibold text-gray-900 truncate" x-text="item.name"></h4>
                                <p class="text-[11px] text-gray-400 mt-0.5" x-text="item.seller"></p>
                            </div>
                            <button @click="$store.cart.removeItem(item.id)"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        <div class="flex items-center justify-between mt-2">
                            {{-- Quantity --}}
                            <div class="flex items-center bg-white rounded-xl border border-gray-200 overflow-hidden">
                                <button @click="$store.cart.updateQuantity(item.id, item.quantity - 1)"
                                        :disabled="$store.cart.loading"
                                        class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <span class="w-8 h-8 flex items-center justify-center text-[13px] font-semibold text-gray-900 border-x border-gray-200"
                                      x-text="item.quantity"></span>
                                <button @click="$store.cart.updateQuantity(item.id, item.quantity + 1)"
                                        :disabled="$store.cart.loading || item.quantity >= item.max_stock"
                                        class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Price --}}
                            <div class="text-right">
                                <p class="text-[14px] font-bold text-gray-900" x-text="'$' + (item.price * item.quantity).toFixed(2)"></p>
                                <template x-if="item.quantity > 1">
                                    <p class="text-[10px] text-gray-400" x-text="'$' + item.price.toFixed(2) + ' each'"></p>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <template x-if="$store.cart.count > 0">
            <div class="border-t border-gray-100 px-6 py-5 space-y-4 bg-white">
                {{-- Coupon --}}
                <div class="flex gap-2">
                    <input type="text" x-model="$store.cart.couponCode"
                           :disabled="$store.cart.couponApplied"
                           placeholder="{{ __('Coupon code') }}"
                           class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 disabled:opacity-50">
                    <template x-if="!$store.cart.couponApplied">
                        <button @click="$store.cart.applyCoupon()"
                                :disabled="$store.cart.loading || !$store.cart.couponCode.trim()"
                                class="px-4 py-2.5 bg-gray-900 text-white text-[12px] font-semibold rounded-xl hover:bg-gray-800 transition-colors disabled:opacity-50">
                            {{ __('Apply') }}
                        </button>
                    </template>
                    <template x-if="$store.cart.couponApplied">
                        <button @click="$store.cart.removeCoupon()"
                                class="px-4 py-2.5 bg-red-50 text-red-600 text-[12px] font-semibold rounded-xl hover:bg-red-100 transition-colors">
                            {{ __('Remove') }}
                        </button>
                    </template>
                </div>

                {{-- Summary --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">{{ __('Subtotal') }}</span>
                        <span class="text-[13px] font-semibold text-gray-900" x-text="$store.cart.formattedSubtotal"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] text-gray-500">{{ __('Shipping') }}</span>
                        <span class="text-[13px] font-semibold" :class="$store.cart.shipping === 0 ? 'text-emerald-600' : 'text-gray-900'" x-text="$store.cart.formattedShipping"></span>
                    </div>
                    <template x-if="$store.cart.couponDiscount > 0">
                        <div class="flex items-center justify-between">
                            <span class="text-[13px] text-gray-500">{{ __('Coupon Discount') }}</span>
                            <span class="text-[13px] font-semibold text-emerald-600" x-text="$store.cart.formattedCouponDiscount"></span>
                        </div>
                    </template>
                    <div class="border-t border-gray-100 pt-2 flex items-center justify-between">
                        <span class="text-[14px] font-bold text-gray-900">{{ __('Total') }}</span>
                        <span class="text-[16px] font-bold text-gray-900" x-text="$store.cart.formattedGrandTotal"></span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3">
                    <a href="/cart" class="flex-1 py-3 bg-gray-100 text-gray-700 text-[13px] font-semibold rounded-xl hover:bg-gray-200 transition-colors text-center">
                        {{ __('View Cart') }}
                    </a>
                    <a href="/checkout" class="flex-1 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors text-center shadow-lg shadow-emerald-600/20">
                        {{ __('Checkout') }}
                    </a>
                </div>
            </div>
        </template>
    </div>
</div>
