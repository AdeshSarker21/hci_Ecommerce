<x-admin.layout title="Shipment {{ $shipment->shipment_number }}" active="shipments">
    <div class="mb-6">
        <a href="{{ route('admin.shipments.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Shipments
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $shipment->shipment_number }}</h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Order #{{ $shipment->order?->order_number ?? 'N/A' }} &middot; {{ $shipment->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if(in_array($shipment->internal_status, ['pending', 'delivery_failed']) && $shipment->courier?->shipment_creation_support)
                    <form method="POST" action="{{ route('admin.shipments.retry', $shipment) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200">Retry Shipment</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.shipments.sync-status', $shipment) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200">Sync Status</button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @if($shipment->error_message)
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-sm font-semibold text-red-800">Shipment Error</p>
            <p class="text-sm text-red-600 mt-1">{{ $shipment->error_message }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Tracking Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking Information</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Shipment #</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->shipment_number }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Consignment ID</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->consignment_id ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Tracking Code</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $shipment->tracking_code ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Invoice #</p><p class="text-sm text-gray-900">{{ $shipment->invoice_number ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Courier</p><p class="text-sm text-gray-900">{{ $shipment->courier?->name ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Delivery Type</p><p class="text-sm text-gray-900 uppercase">{{ $shipment->delivery_type }}</p></div>
                </div>
            </div>

            {{-- Customer & Recipient --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recipient Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Customer</p><p class="text-sm font-semibold text-gray-900">{{ $shipment->order?->user?->name ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Customer Email</p><p class="text-sm text-gray-900">{{ $shipment->order?->user?->email ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Recipient Name</p><p class="text-sm font-semibold text-gray-900">{{ $shipment->recipient_name }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Recipient Phone</p><p class="text-sm text-gray-900">{{ $shipment->recipient_phone }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs font-medium text-gray-500">Address</p><p class="text-sm text-gray-900">{{ $shipment->recipient_address }}</p></div>
                    @if($shipment->recipient_city)<div><p class="text-xs font-medium text-gray-500">City</p><p class="text-sm text-gray-900">{{ $shipment->recipient_city }}</p></div>@endif
                    @if($shipment->recipient_area)<div><p class="text-xs font-medium text-gray-500">Area</p><p class="text-sm text-gray-900">{{ $shipment->recipient_area }}</p></div>@endif
                </div>
            </div>

            {{-- Seller --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Seller</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Store</p><p class="text-sm font-semibold text-gray-900">{{ $shipment->seller?->store_name ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Phone</p><p class="text-sm text-gray-900">{{ $shipment->seller?->contact_phone ?? '-' }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs font-medium text-gray-500">Address</p><p class="text-sm text-gray-900">{{ $shipment->seller?->business_address ?? '-' }}</p></div>
                </div>
            </div>

            {{-- COD Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">COD Information</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">COD Amount</p><p class="text-lg font-bold text-gray-900">${{ number_format($shipment->cod_amount, 2) }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Shipping Fee</p><p class="text-lg font-bold text-gray-900">${{ number_format($shipment->shipping_fee, 2) }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">COD Fee</p><p class="text-lg font-bold text-red-600">${{ number_format($shipment->cod_fee, 2) }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Total Collectable</p><p class="text-lg font-bold text-emerald-600">${{ number_format($shipment->total_collectable, 2) }}</p></div>
                </div>
            </div>

            {{-- Payment Collection Status --}}
            @if($shipment->order)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment & Collection</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500">Order Status</p>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->order->status_badge }}">{{ ucfirst($shipment->order->status) }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Payment Status</p>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->order->payment_status_badge }}">{{ ucfirst($shipment->order->payment_status) }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">Seller Payment</p>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->order->seller_payment_status_badge }}">{{ $shipment->order->seller_payment_status_label }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Order Items --}}
            @if($shipment->order?->items?->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-2">
                        @foreach($shipment->order->items as $item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                                    <p class="text-xs text-gray-500">SKU: {{ $item->product_sku }} &middot; Qty: {{ $item->quantity }}</p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($item->total, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Shipment Timeline --}}
            @if($shipment->status_history)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipment Timeline</h3>
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

            {{-- Courier API Response --}}
            @if($shipment->courier_response)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Courier API Response</h3>
                    <pre class="text-xs bg-gray-50 rounded-lg p-4 text-gray-700 overflow-x-auto max-h-64">{{ json_encode($shipment->courier_response, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Timestamps --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timestamps</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Created</span><span class="text-sm text-gray-900">{{ $shipment->created_at->format('M d, Y h:i A') }}</span></div>
                    @if($shipment->shipped_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Shipped</span><span class="text-sm text-gray-900">{{ $shipment->shipped_at->format('M d, Y h:i A') }}</span></div>@endif
                    @if($shipment->in_transit_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">In Transit</span><span class="text-sm text-gray-900">{{ $shipment->in_transit_at->format('M d, Y h:i A') }}</span></div>@endif
                    @if($shipment->out_for_delivery_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Out for Delivery</span><span class="text-sm text-gray-900">{{ $shipment->out_for_delivery_at->format('M d, Y h:i A') }}</span></div>@endif
                    @if($shipment->delivered_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Delivered</span><span class="text-sm text-green-600 font-semibold">{{ $shipment->delivered_at->format('M d, Y h:i A') }}</span></div>@endif
                    @if($shipment->returned_at)<div class="flex items-center justify-between"><span class="text-sm text-gray-500">Returned</span><span class="text-sm text-red-600 font-semibold">{{ $shipment->returned_at->format('M d, Y h:i A') }}</span></div>@endif
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Last Synced</span><span class="text-sm text-gray-900">{{ $shipment->last_synced_at?->diffForHumans() ?? 'Never' }}</span></div>
                </div>
            </div>

            {{-- Update Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h3>
                <form method="POST" action="{{ route('admin.shipments.update-status', $shipment) }}">
                    @csrf @method('PATCH')
                    <div class="space-y-3">
                        <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            @foreach(\App\Models\Shipment::statuses() as $key => $label)
                                <option value="{{ $key }}" {{ ($shipment->internal_status ?? $shipment->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="note" placeholder="Optional note..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Update Status</button>
                    </div>
                </form>
            </div>

            {{-- Activity Log --}}
            @if(isset($activities) && $activities->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Log</h3>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($activities as $activity)
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs font-medium text-gray-900">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }} &middot; {{ $activity->user?->name ?? 'System' }}</p>
                                @if($activity->properties && count($activity->properties))
                                    <p class="text-xs text-gray-400 mt-1 font-mono">{{ json_encode(array_filter($activity->properties, fn($v) => !is_array($v))) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Webhook Logs --}}
            @if($shipment->webhookLogs?->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Webhook Events</h3>
                    <div class="space-y-2">
                        @foreach($shipment->webhookLogs as $log)
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-900">{{ $log->event_type }}</span>
                                    <span class="text-xs {{ $log->is_processed ? 'text-green-600' : 'text-red-600' }}">{{ $log->is_processed ? 'Processed' : 'Failed' }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
