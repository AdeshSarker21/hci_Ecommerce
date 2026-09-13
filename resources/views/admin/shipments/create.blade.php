<x-admin.layout title="Create Shipment" active="shipments">
    <div class="mb-6">
        <a href="{{ route('admin.shipments.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Shipments
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Create Shipment for Order #{{ $order->order_number }}</h2>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Order #</p><p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Total</p><p class="text-sm font-semibold text-gray-900">${{ number_format($order->total, 2) }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Payment</p><p class="text-sm text-gray-900 uppercase">{{ $order->payment_method }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Status</p><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->status_badge }}">{{ ucfirst($order->status) }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h3>
                @php $addr = $order->shipping_address ?? []; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Name</p><p class="text-sm font-semibold text-gray-900">{{ $addr['name'] ?? $order->user?->name ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Phone</p><p class="text-sm text-gray-900">{{ $addr['phone'] ?? $order->user?->phone ?? '-' }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs font-medium text-gray-500">Address</p><p class="text-sm text-gray-900">{{ $addr['address'] ?? $addr['address_line_1'] ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">City</p><p class="text-sm text-gray-900">{{ $addr['city'] ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Area</p><p class="text-sm text-gray-900">{{ $addr['area'] ?? '-' }}</p></div>
                </div>
            </div>

            @if($order->items?->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-2">
                        @foreach($order->items as $item)
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
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.shipments.store') }}">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create Shipment</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Courier <span class="text-red-500">*</span></label>
                        <select name="courier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            <option value="">Select courier...</option>
                            @foreach($couriers as $courier)
                                <option value="{{ $courier->id }}" {{ old('courier_id') == $courier->id ? 'selected' : '' }}>
                                    {{ $courier->name }}
                                    @if($courier->cod_fee > 0) (COD Fee: ${{ $courier->cod_fee }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('courier_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea name="note" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Optional note...">{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Create Shipment</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
