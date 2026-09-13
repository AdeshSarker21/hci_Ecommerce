<x-admin.layout title="Delivery Status" active="delivery-status">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Delivery Status</h2>
        <p class="text-sm text-gray-500 mt-1">Real-time overview of all delivery statuses across couriers</p>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-7 gap-3 mb-6">
        @php
            $statusColors = [
                'total' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'label' => 'Total'],
                'pending' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'label' => 'Pending'],
                'picked_up' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'label' => 'Picked Up'],
                'in_transit' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'label' => 'In Transit'],
                'out_for_delivery' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'label' => 'Out for Delivery'],
                'delivered' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'label' => 'Delivered'],
                'returned' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'label' => 'Returned'],
            ];
        @endphp
        @foreach($statusColors as $key => $info)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center {{ request('status') === $key ? 'ring-2 ring-indigo-500' : '' }}">
                <a href="{{ $key === 'total' ? route('admin.shipments.delivery-status') : route('admin.shipments.delivery-status', ['status' => $key]) }}" class="block">
                    <p class="text-xl font-bold {{ $info['text'] }}">{{ $stats[$key] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $info['label'] }}</p>
                </a>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Shipment #, tracking, order #..."
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Courier</label>
                <select name="courier_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Couriers</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}" {{ request('courier_id') == $courier->id ? 'selected' : '' }}>{{ $courier->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
            @if(request()->hasAny(['search', 'courier_id', 'status']))
                <a href="{{ route('admin.shipments.delivery-status') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($shipments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shipment #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">COD</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($shipments as $shipment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-indigo-600">{{ $shipment->shipment_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">#{{ $shipment->order?->order_number ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $shipment->courier?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $shipment->seller?->store_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $shipment->recipient_name }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($shipment->cod_amount, 2) }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $shipment->updated_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $shipments->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <p class="text-gray-500 text-sm">No shipments found</p>
            </div>
        @endif
    </div>
</x-admin.layout>
