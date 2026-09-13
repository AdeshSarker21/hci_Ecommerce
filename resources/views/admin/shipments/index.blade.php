<x-admin.layout title="Shipments" active="shipments">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Shipments</h2>
                <p class="text-sm text-gray-500 mt-1">Manage and track all courier shipments</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700 font-medium">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3 mb-6">
        @php
            $statCards = [
                ['key' => 'total', 'label' => 'Total', 'color' => 'indigo'],
                ['key' => 'pending', 'label' => 'Pending', 'color' => 'yellow'],
                ['key' => 'confirmed', 'label' => 'Confirmed', 'color' => 'blue'],
                ['key' => 'in_transit', 'label' => 'In Transit', 'color' => 'purple'],
                ['key' => 'delivered', 'label' => 'Delivered', 'color' => 'green'],
                ['key' => 'returned', 'label' => 'Returned', 'color' => 'red'],
                ['key' => 'failed', 'label' => 'Failed', 'color' => 'red'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center {{ request('status') === $card['key'] ? 'ring-2 ring-indigo-500' : '' }}">
                <a href="{{ $card['key'] === 'total' ? route('admin.shipments.index') : route('admin.shipments.index', ['status' => $card['key']]) }}" class="block">
                    <p class="text-xl font-bold text-{{ $card['color'] }}-600">{{ $stats[$card['key']] ?? 0 }}</p>
                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $card['label'] }}</p>
                </a>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Shipment #, tracking, order #, recipient..."
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
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Seller</label>
                <select name="seller_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Sellers</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>{{ $seller->store_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All</option>
                    @foreach(\App\Models\Shipment::statuses() as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Filter</button>
            @if(request()->hasAny(['search', 'courier_id', 'seller_id', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.shipments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($shipments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shipment</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courier</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consignment</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">COD</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivered</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($shipments as $shipment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 font-mono">{{ $shipment->shipment_number }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">#{{ $shipment->order?->order_number ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $shipment->seller?->store_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $shipment->recipient_name }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        @if($shipment->courier?->logo)
                                            <img src="{{ $shipment->courier->logo }}" class="w-4 h-4 rounded" alt="">
                                        @endif
                                        <span class="text-xs text-gray-600">{{ $shipment->courier?->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 font-mono">{{ $shipment->consignment_id ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">${{ number_format($shipment->cod_amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $shipment->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $shipment->delivered_at ? $shipment->delivered_at->format('M d, Y') : '-' }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('admin.shipments.show', $shipment) }}" class="text-indigo-600 hover:text-indigo-700 font-medium text-xs">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $shipments->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-500 text-sm">No shipments found</p>
            </div>
        @endif
    </div>
</x-admin.layout>
