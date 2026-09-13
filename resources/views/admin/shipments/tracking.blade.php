<x-admin.layout title="Shipment Tracking" active="tracking">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Shipment Tracking</h2>
        <p class="text-sm text-gray-500 mt-1">Look up any shipment by tracking number, consignment ID, or order number</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[300px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tracking Query</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Enter shipment #, consignment ID, tracking code, or order #..."
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                       autofocus>
            </div>
            <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Track
            </button>
        </form>
    </div>

    @if($searched)
        @if($shipment)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Shipment Details</h3>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div><p class="text-xs font-medium text-gray-500">Shipment #</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->shipment_number }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Consignment ID</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->consignment_id ?? '-' }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Tracking Code</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->tracking_code ?? '-' }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Order #</p><p class="text-sm text-indigo-600 font-medium">#{{ $shipment->order?->order_number ?? 'N/A' }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Courier</p><p class="text-sm text-gray-900">{{ $shipment->courier?->name ?? $shipment->courier_name ?? 'N/A' }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Seller</p><p class="text-sm text-gray-900">{{ $shipment->seller?->store_name ?? 'N/A' }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">COD Amount</p><p class="text-sm font-bold text-gray-900">${{ number_format($shipment->cod_amount, 2) }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Delivery Type</p><p class="text-sm text-gray-900 uppercase">{{ $shipment->delivery_type }}</p></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recipient</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div><p class="text-xs font-medium text-gray-500">Name</p><p class="text-sm font-semibold text-gray-900">{{ $shipment->recipient_name }}</p></div>
                            <div><p class="text-xs font-medium text-gray-500">Phone</p><p class="text-sm text-gray-900">{{ $shipment->recipient_phone }}</p></div>
                            <div class="sm:col-span-2"><p class="text-xs font-medium text-gray-500">Address</p><p class="text-sm text-gray-900">{{ $shipment->recipient_address }}</p></div>
                            @if($shipment->recipient_city)<div><p class="text-xs font-medium text-gray-500">City</p><p class="text-sm text-gray-900">{{ $shipment->recipient_city }}</p></div>@endif
                            @if($shipment->recipient_area)<div><p class="text-xs font-medium text-gray-500">Area</p><p class="text-sm text-gray-900">{{ $shipment->recipient_area }}</p></div>@endif
                        </div>
                    </div>

                    @if($shipment->status_history)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status History</h3>
                            <div class="space-y-3">
                                @foreach(array_reverse($shipment->status_history) as $entry)
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $entry['to'] ?? '')) }}</p>
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
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Timestamps</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Created</span><span class="text-sm text-gray-900">{{ $shipment->created_at->format('M d, Y h:i A') }}</span></div>
                            @if($shipment->shipped_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Shipped</span><span class="text-sm text-gray-900">{{ $shipment->shipped_at->format('M d, Y h:i A') }}</span></div>@endif
                            @if($shipment->in_transit_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">In Transit</span><span class="text-sm text-gray-900">{{ $shipment->in_transit_at->format('M d, Y h:i A') }}</span></div>@endif
                            @if($shipment->out_for_delivery_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Out for Delivery</span><span class="text-sm text-gray-900">{{ $shipment->out_for_delivery_at->format('M d, Y h:i A') }}</span></div>@endif
                            @if($shipment->delivered_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Delivered</span><span class="text-sm text-green-600 font-semibold">{{ $shipment->delivered_at->format('M d, Y h:i A') }}</span></div>@endif
                            @if($shipment->returned_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Returned</span><span class="text-sm text-red-600 font-semibold">{{ $shipment->returned_at->format('M d, Y h:i A') }}</span></div>@endif
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-2">
                            <a href="{{ route('admin.shipments.show', $shipment) }}" class="block w-full text-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">View Full Details</a>
                            <form method="POST" action="{{ route('admin.shipments.sync-status', $shipment) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Sync Status from Courier</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-gray-900 font-semibold text-lg mb-1">No shipment found</p>
                <p class="text-gray-500 text-sm">No shipment matched "<span class="font-mono">{{ request('q') }}</span>". Try a different search term.</p>
            </div>
        @endif
    @endif
</x-admin.layout>
