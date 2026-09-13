<x-storefront.layout :title="__('Track Order') . ' - ' . $order->order_number">
    <div class="min-h-screen bg-[#fafafa] py-8 lg:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="mb-6">
            <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to Orders
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Track Order #{{ $order->order_number }}</h2>
        </div>

        {{-- Order Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500">Order Status</p>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->status_badge }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Payment</p>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->payment_status_badge }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Total</p><p class="text-sm font-bold text-gray-900">${{ number_format($order->total, 2) }}</p></div>
                <div><p class="text-xs font-medium text-gray-500">Seller</p><p class="text-sm text-gray-900">{{ $order->seller?->store_name ?? '-' }}</p></div>
            </div>
        </div>

        @if($shipment)
            {{-- Shipment Tracking --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipment Tracking</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    <div><p class="text-xs font-medium text-gray-500">Courier</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            @if($shipment->courier?->logo)
                                <img src="{{ $shipment->courier->logo }}" class="w-5 h-5 rounded" alt="">
                            @endif
                            <p class="text-sm font-semibold text-gray-900">{{ $shipment->courier?->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div><p class="text-xs font-medium text-gray-500">Tracking #</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->consignment_id ?? $shipment->tracking_code ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Status</p>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span>
                    </div>
                    <div><p class="text-xs font-medium text-gray-500">Delivery Type</p><p class="text-sm text-gray-900 uppercase">{{ $shipment->delivery_type }}</p></div>
                </div>

                {{-- Timeline --}}
                @if($shipment->status_history && count($shipment->status_history))
                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Tracking Timeline</h4>
                        <div class="space-y-3">
                            @foreach(array_reverse($shipment->status_history) as $entry)
                                <div class="flex items-start gap-3">
                                    <div class="w-3 h-3 rounded-full @if(($entry['to'] ?? '') === 'delivered') bg-green-500 @elseif(($entry['to'] ?? '') === 'returned') bg-red-500 @else bg-indigo-500 @endif mt-0.5 flex-shrink-0"></div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ \App\Models\Shipment::statuses()[$entry['to'] ?? ''] ?? ucfirst(str_replace('_', ' ', $entry['to'] ?? '')) }}</p>
                                        <p class="text-xs text-gray-500">{{ $entry['timestamp'] ?? '' }}</p>
                                        @if(!empty($entry['details']))<p class="text-xs text-gray-600 mt-0.5">{{ $entry['details'] }}</p>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No tracking events yet. Your shipment is being processed.</p>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-500">No shipment has been created for this order yet.</p>
            </div>
        @endif

        {{-- Order Items --}}
        @if($order->items?->count())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                <div class="space-y-2">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-500">Qty: {{ $item->quantity }} &middot; ${{ number_format($item->unit_price, 2) }} each</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">${{ number_format($item->total, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    </div>
</x-storefront.layout>
