<x-seller.layout title="Shipment {{ $shipment->shipment_number }}" active="shipments">
    <div class="mb-6">
        <a href="{{ route('seller.shipments.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Shipments
        </a>
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-gray-900">{{ $shipment->shipment_number }}</h2>
            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-1">Order #{{ $shipment->order?->order_number ?? 'N/A' }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Tracking --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Courier</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            @if($shipment->courier?->logo)
                                <img src="{{ $shipment->courier->logo }}" class="w-5 h-5 rounded" alt="">
                            @endif
                            <p class="text-sm font-semibold text-gray-900">{{ $shipment->courier?->name ?? '-' }}</p>
                        </div>
                    </div>
                    <div><p class="text-xs font-medium text-gray-500">Consignment ID</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->consignment_id ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Tracking Code</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->tracking_code ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Delivery Type</p><p class="text-sm text-gray-900 uppercase">{{ $shipment->delivery_type }}</p></div>
                </div>
            </div>

            {{-- Recipient --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recipient</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Name</p><p class="text-sm font-semibold text-gray-900">{{ $shipment->recipient_name }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Phone</p><p class="text-sm text-gray-900">{{ $shipment->recipient_phone }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs font-medium text-gray-500">Address</p><p class="text-sm text-gray-900">{{ $shipment->recipient_address }}</p></div>
                    @if($shipment->recipient_city)<div><p class="text-xs font-medium text-gray-500">City</p><p class="text-sm text-gray-900">{{ $shipment->recipient_city }}</p></div>@endif
                </div>
            </div>

            {{-- COD --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">COD Information</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">COD Amount</p><p class="text-lg font-bold text-gray-900">${{ number_format($shipment->cod_amount, 2) }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Shipping Fee</p><p class="text-lg font-bold text-gray-900">${{ number_format($shipment->shipping_fee, 2) }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Total Collectable</p><p class="text-lg font-bold text-emerald-600">${{ number_format($shipment->total_collectable, 2) }}</p></div>
                </div>
            </div>

            {{-- Timeline --}}
            @if($shipment->status_history && count($shipment->status_history))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking Timeline</h3>
                    <div class="space-y-3">
                        @foreach(array_reverse($shipment->status_history) as $entry)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="w-2.5 h-2.5 rounded-full @if(($entry['to'] ?? '') === 'delivered') bg-green-500 @elseif(($entry['to'] ?? '') === 'returned') bg-red-500 @else bg-indigo-500 @endif mt-1.5 flex-shrink-0"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ \App\Models\Shipment::statuses()[$entry['to'] ?? ''] ?? ucfirst(str_replace('_', ' ', $entry['to'] ?? '')) }}</p>
                                    <p class="text-xs text-gray-500">{{ $entry['timestamp'] ?? '' }}</p>
                                    @if(!empty($entry['details']))<p class="text-xs text-gray-600 mt-1">{{ $entry['details'] }}</p>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Timestamps --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timestamps</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Created</span><span class="text-sm text-gray-900">{{ $shipment->created_at->format('M d, Y') }}</span></div>
                    @if($shipment->delivered_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Delivered</span><span class="text-sm text-green-600 font-semibold">{{ $shipment->delivered_at->format('M d, Y') }}</span></div>@endif
                    @if($shipment->returned_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Returned</span><span class="text-sm text-red-600 font-semibold">{{ $shipment->returned_at->format('M d, Y') }}</span></div>@endif
                </div>
            </div>

            {{-- Order Summary --}}
            @if($shipment->order)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Order #</span><span class="font-medium text-gray-900">{{ $shipment->order->order_number }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Total</span><span class="font-medium text-gray-900">${{ number_format($shipment->order->total, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-gray-500">Payment</span><span class="uppercase text-gray-900">{{ $shipment->order->payment_method }}</span></div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Status</span>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->order->status_badge }}">{{ ucfirst($shipment->order->status) }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-seller.layout>
