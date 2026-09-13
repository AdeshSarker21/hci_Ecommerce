<x-storefront.layout :title="__('Order Confirmation') . ' - ' . $order->order_number">
    @php
        $locale = app()->getLocale();
        $shippingAddress = $order->shipping_address ?? [];
    @endphp

    <div class="min-h-screen bg-[#fafafa] py-8 lg:py-12" x-data="{ loaded: false }" x-init="$nextTick(() => { loaded = true; })">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">

            {{-- Success Header --}}
            <div class="text-center mb-8 confirmation-header" style="opacity: 0;">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-[26px] sm:text-[30px] font-bold text-gray-900 mb-2">{{ __('Thank You for Your Order!') }}</h1>
                <p class="text-[14px] text-gray-500">{{ __('Your order has been placed successfully.') }}</p>
            </div>

            {{-- Order Summary Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6 confirmation-card" style="opacity: 0;">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-[12px] text-gray-400 uppercase tracking-wider font-medium">{{ __('Order Number') }}</p>
                            <p class="text-[16px] font-bold text-gray-900">{{ $order->order_number }}</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-[12px] text-gray-400 uppercase tracking-wider font-medium">{{ __('Order Date') }}</p>
                            <p class="text-[13px] font-medium text-gray-700">{{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Status & Payment --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $order->payment_status === 'paid' ? 'bg-emerald-100' : 'bg-amber-100' }}">
                                <svg class="w-5 h-5 {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400 font-medium">{{ __('Payment Status') }}</p>
                                <p class="text-[13px] font-semibold text-gray-900">
                                    {{ $order->payment_status === 'paid' ? __('Paid') : ($order->payment_status === 'pending' ? __('Pending') : ucfirst($order->payment_status)) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-100">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400 font-medium">{{ __('Payment Method') }}</p>
                                <p class="text-[13px] font-semibold text-gray-900">
                                    {{ $order->payment_method === 'cod' ? __('Cash on Delivery') : __('Online Payment') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    @if(!empty($shippingAddress))
                        <div class="p-4 bg-gray-50 rounded-xl mb-6">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-[12px] font-semibold text-gray-700 uppercase tracking-wider">{{ __('Shipping Address') }}</p>
                            </div>
                            <p class="text-[13px] font-medium text-gray-900">{{ $shippingAddress['name'] ?? '' }}</p>
                            <p class="text-[12px] text-gray-500 mt-1">
                                {{ $shippingAddress['address_line_1'] ?? '' }}@if(!empty($shippingAddress['address_line_2'])), {{ $shippingAddress['address_line_2'] }}@endif
                            </p>
                            <p class="text-[12px] text-gray-500">
                                {{ $shippingAddress['city'] ?? '' }}@if(!empty($shippingAddress['state'])), {{ $shippingAddress['state'] }}@endif @if(!empty($shippingAddress['postal_code'])), {{ $shippingAddress['postal_code'] }}@endif
                            </p>
                            @if(!empty($shippingAddress['phone']))
                                <p class="text-[12px] text-gray-500 mt-1">{{ $shippingAddress['phone'] }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Products --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-[12px] font-semibold text-gray-700 uppercase tracking-wider">{{ __('Order Items') }}</p>
                        </div>

                        {{-- Seller Group --}}
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                                <p class="text-[12px] font-semibold text-gray-700">
                                    {{ __('Sold by') }}: {{ $order->seller?->store_name ?? __('Unknown Seller') }}
                                </p>
                            </div>
                            @foreach($order->items as $item)
                                <div class="flex items-center gap-4 px-4 py-3 @if(!$loop->last) border-b border-gray-50 @endif">
                                    <div class="w-14 h-14 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                        @if($item->product?->primary_image)
                                            <img src="{{ asset('storage/' . $item->product->primary_image) }}" alt="{{ $locale === 'bn' && $item->product?->name_bn ? $item->product->name_bn : $item->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-medium text-gray-900 truncate">
                                            {{ $locale === 'bn' && $item->product?->name_bn ? $item->product->name_bn : $item->product_name }}
                                        </p>
                                        @if($item->product_sku)
                                            <p class="text-[11px] text-gray-400">{{ $item->product_sku }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-[13px] font-semibold text-gray-900">${{ number_format($item->total, 2) }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Order Total --}}
                    <div class="border-t border-gray-100 pt-4 space-y-2">
                        <div class="flex justify-between text-[13px]">
                            <span class="text-gray-500">{{ __('Subtotal') }}</span>
                            <span class="text-gray-700">${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->shipping_cost > 0)
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-500">{{ __('Shipping') }}</span>
                                <span class="text-gray-700">${{ number_format($order->shipping_cost, 2) }}</span>
                            </div>
                        @endif
                        @if($order->discount > 0)
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-500">{{ __('Discount') }}</span>
                                <span class="text-emerald-600">-${{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-[15px] font-bold pt-2 border-t border-gray-100">
                            <span class="text-gray-900">{{ __('Total') }}</span>
                            <span class="text-gray-900">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 confirmation-actions" style="opacity: 0;">
                <a href="{{ route('account.orders.show', $order) }}" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-emerald-600 text-white text-[14px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('View Order Details') }}
                </a>
                <a href="{{ route('search') }}" class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-white text-gray-700 text-[14px] font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    {{ __('Continue Shopping') }}
                </a>
            </div>

            {{-- Help Note --}}
            <div class="text-center mt-8 confirmation-help" style="opacity: 0;">
                <p class="text-[12px] text-gray-400">
                    {{ __('Questions about your order?') }}
                    <a href="#" class="text-emerald-600 font-medium hover:underline">{{ __('Contact Support') }}</a>
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.confirmation-header, .confirmation-card, .confirmation-actions, .confirmation-help').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            tl.to('.confirmation-header', { opacity: 1, y: 0, duration: 0.6, delay: 0.1 })
              .from('.confirmation-header > div:first-child', { scale: 0, duration: 0.5, ease: 'back.out(1.7)' }, '-=0.3')
              .to('.confirmation-card', { opacity: 1, y: 0, duration: 0.5 }, '-=0.2')
              .to('.confirmation-actions', { opacity: 1, y: 0, duration: 0.4 }, '-=0.2')
              .to('.confirmation-help', { opacity: 1, duration: 0.3 }, '-=0.1');

            gsap.set('.confirmation-header', { y: 20 });
            gsap.set('.confirmation-card', { y: 20 });
            gsap.set('.confirmation-actions', { y: 20 });
            tl.play();
        });
    </script>
    @endpush
</x-storefront.layout>
