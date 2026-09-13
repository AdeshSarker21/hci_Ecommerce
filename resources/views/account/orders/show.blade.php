<x-storefront.layout :title="__('Order Details') . ' - ' . $order->order_number">
    @php
        $locale = app()->getLocale();
        $shippingAddress = $order->shipping_address ?? [];
    @endphp

    <div class="min-h-screen bg-[#fafafa] py-8 lg:py-12" x-data>
        <div class="max-w-4xl mx-auto px-4 sm:px-6">

            {{-- Back Link --}}
            <div class="mb-6">
                <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-gray-500 hover:text-emerald-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back to Orders') }}
                </a>
            </div>

            {{-- Page Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 order-detail-header" style="opacity: 0;">
                <div>
                    <h1 class="text-[22px] sm:text-[26px] font-bold text-gray-900">{{ __('Order Details') }}</h1>
                    <p class="text-[13px] text-gray-500 mt-1">{{ $order->order_number }} &middot; {{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[12px] font-semibold {{ $order->status_badge }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[12px] font-semibold {{ $order->payment_status_badge }}">
                        {{ $order->payment_status === 'paid' ? __('Paid') : ucfirst($order->payment_status) }}
                    </span>
                    <a href="{{ route('account.orders.track', $order) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        {{ __('Track') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left Column: Timeline + Items --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Status Timeline --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 order-detail-timeline" style="opacity: 0;">
                        <h2 class="text-[14px] font-bold text-gray-900 mb-5">{{ __('Order Status') }}</h2>
                        <div class="relative">
                            @foreach($timeline as $index => $step)
                                @php
                                    $stepKey = $step['key'];
                                    $stepCompleted = $step['completed'];
                                    $stepCurrent = $step['current'];
                                    $stepTimestamp = $step['timestamp'];
                                    $stepLabel = $step['label'];
                                    $hasMore = $index < count($timeline) - 1;
                                    $nextCompleted = $hasMore ? $timeline[$index + 1]['completed'] : false;
                                @endphp
                                <div class="flex gap-4 timeline-step {{ $hasMore ? 'pb-6' : '' }}" style="opacity: 0;">
                                    {{-- Icon --}}
                                    <div class="relative flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center z-10 relative
                                            {{ $stepCompleted ? ($stepCurrent ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-emerald-500 text-white') : 'bg-gray-100 text-gray-400' }}">
                                            @if($stepKey === 'pending')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            @elseif($stepKey === 'processing')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @elseif($stepKey === 'shipped')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                            @elseif($stepKey === 'delivered')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                            @elseif($stepKey === 'cancelled')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        {{-- Connector Line --}}
                                        @if($hasMore)
                                            <div class="absolute left-5 top-10 w-0.5 h-full {{ $stepCompleted && $nextCompleted ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>
                                        @endif
                                    </div>
                                    {{-- Label --}}
                                    <div class="pt-1.5">
                                        <p class="text-[13px] font-semibold {{ $stepCompleted ? 'text-gray-900' : 'text-gray-400' }}">{{ $stepLabel }}</p>
                                        @if($stepTimestamp)
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $stepTimestamp->format('M d, Y \a\t h:i A') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden order-detail-items" style="opacity: 0;">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                            <h2 class="text-[14px] font-bold text-gray-900">{{ __('Order Items') }}</h2>
                        </div>

                        {{-- Seller Group --}}
                        <div class="border-b border-gray-100">
                            <div class="px-6 py-2.5 bg-gray-50/50">
                                <p class="text-[12px] font-semibold text-gray-600">
                                    {{ __('Sold by') }}: <span class="text-gray-900">{{ $order->seller?->store_name ?? __('Unknown Seller') }}</span>
                                </p>
                            </div>
                            @foreach($order->items as $item)
                                <div class="flex items-center gap-4 px-6 py-4 @if(!$loop->last) border-b border-gray-50 @endif">
                                    <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                        @if($item->product?->primary_image)
                                            <img src="{{ asset('storage/' . $item->product->primary_image) }}" alt="{{ $locale === 'bn' && $item->product?->name_bn ? $item->product->name_bn : $item->product_name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ $item->product ? route('product.show', $item->product->slug) : '#' }}" class="text-[13px] font-medium text-gray-900 hover:text-emerald-600 transition-colors truncate block">
                                            {{ $locale === 'bn' && $item->product?->name_bn ? $item->product->name_bn : $item->product_name }}
                                        </a>
                                        @if($item->product_sku)
                                            <p class="text-[11px] text-gray-400 mt-0.5">SKU: {{ $item->product_sku }}</p>
                                        @endif
                                        <p class="text-[12px] text-gray-500 mt-0.5">{{ __('Qty') }}: {{ $item->quantity }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-[14px] font-bold text-gray-900">${{ number_format($item->total, 2) }}</p>
                                        <p class="text-[11px] text-gray-400">${{ number_format($item->unit_price, 2) }} {{ __('each') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Shipment Tracking --}}
                    @if($order->tracking_number || $order->courier_name)
                        <div class="bg-white rounded-2xl border border-gray-100 p-6 order-detail-tracking" style="opacity: 0;">
                            <h2 class="text-[14px] font-bold text-gray-900 mb-4">{{ __('Shipment Information') }}</h2>
                            <div class="space-y-3">
                                @if($order->courier_name)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-gray-400">{{ __('Courier') }}</p>
                                            <p class="text-[13px] font-semibold text-gray-900">{{ $order->courier_name }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($order->tracking_number)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-gray-400">{{ __('Tracking Number') }}</p>
                                            <p class="text-[13px] font-semibold text-gray-900 font-mono">{{ $order->tracking_number }}</p>
                                        </div>
                                    </div>
                                @endif
                                @if($order->tracking_url)
                                    <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        {{ __('Track Shipment') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Summary --}}
                <div class="space-y-6">

                    {{-- Order Summary --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 order-detail-summary" style="opacity: 0;">
                        <h2 class="text-[14px] font-bold text-gray-900 mb-4">{{ __('Order Summary') }}</h2>
                        <div class="space-y-3">
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
                            <div class="flex justify-between text-[15px] font-bold pt-3 border-t border-gray-100">
                                <span class="text-gray-900">{{ __('Total') }}</span>
                                <span class="text-gray-900">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 order-detail-payment" style="opacity: 0;">
                        <h2 class="text-[14px] font-bold text-gray-900 mb-4">{{ __('Payment') }}</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-500">{{ __('Method') }}</span>
                                <span class="text-gray-700 font-medium">{{ $order->payment_method === 'cod' ? __('Cash on Delivery') : __('Online Payment') }}</span>
                            </div>
                            <div class="flex justify-between text-[13px]">
                                <span class="text-gray-500">{{ __('Status') }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $order->payment_status_badge }}">
                                    {{ $order->payment_status === 'paid' ? __('Paid') : ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            @if($order->paid_at)
                                <div class="flex justify-between text-[13px]">
                                    <span class="text-gray-500">{{ __('Paid At') }}</span>
                                    <span class="text-gray-700">{{ $order->paid_at->format('M d, Y h:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    @if(!empty($shippingAddress))
                        <div class="bg-white rounded-2xl border border-gray-100 p-6 order-detail-address" style="opacity: 0;">
                            <h2 class="text-[14px] font-bold text-gray-900 mb-4">{{ __('Shipping Address') }}</h2>
                            <div class="space-y-1">
                                <p class="text-[13px] font-semibold text-gray-900">{{ $shippingAddress['name'] ?? '' }}</p>
                                <p class="text-[12px] text-gray-500">{{ $shippingAddress['address_line_1'] ?? '' }}</p>
                                @if(!empty($shippingAddress['address_line_2']))
                                    <p class="text-[12px] text-gray-500">{{ $shippingAddress['address_line_2'] }}</p>
                                @endif
                                <p class="text-[12px] text-gray-500">
                                    {{ $shippingAddress['city'] ?? '' }}@if(!empty($shippingAddress['state'])), {{ $shippingAddress['state'] }}@endif @if(!empty($shippingAddress['postal_code'])), {{ $shippingAddress['postal_code'] }}@endif
                                </p>
                                @if(!empty($shippingAddress['phone']))
                                    <p class="text-[12px] text-gray-500 mt-2">{{ $shippingAddress['phone'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Estimated Delivery --}}
                    @if(in_array($order->status, array('pending', 'processing', 'shipped')))
                        <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-6 order-detail-eta" style="opacity: 0;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[12px] text-emerald-700 font-medium">{{ __('Estimated Delivery') }}</p>
                                    <p class="text-[14px] font-bold text-emerald-800">
                                        @if($order->shipped_at)
                                            {{ $order->shipped_at->addDays(3)->format('M d, Y') }}
                                        @elseif($order->processing_at)
                                            {{ $order->processing_at->addDays(5)->format('M d, Y') }}
                                        @else
                                            {{ $order->created_at->addDays(7)->format('M d, Y') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.order-detail-header, .order-detail-timeline, .order-detail-items, .order-detail-tracking, .order-detail-summary, .order-detail-payment, .order-detail-address, .order-detail-eta, .timeline-step').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.order-detail-header', '.order-detail-timeline', '.order-detail-items', '.order-detail-tracking', '.order-detail-summary', '.order-detail-payment', '.order-detail-address', '.order-detail-eta'], { y: 20 });

            tl.to('.order-detail-header', { opacity: 1, y: 0, duration: 0.5 })
              .to('.order-detail-timeline', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.timeline-step', { opacity: 1, y: 0, duration: 0.3, stagger: 0.1 }, '-=0.3')
              .to('.order-detail-items', { opacity: 1, y: 0, duration: 0.5 }, '-=0.2')
              .to('.order-detail-tracking', { opacity: 1, y: 0, duration: 0.4 }, '-=0.3')
              .to(['.order-detail-summary', '.order-detail-payment', '.order-detail-address', '.order-detail-eta'], { opacity: 1, y: 0, duration: 0.4, stagger: 0.1 }, '-=0.3');
        });
    </script>
    @endpush
</x-storefront.layout>
