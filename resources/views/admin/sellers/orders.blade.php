<x-admin.layout title="Orders - {{ $seller->store_name }}" active="sellers">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.sellers.show', $seller) }}" class="hover:text-indigo-600">{{ $seller->store_name }}</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-medium">Orders</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order number..."
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <select name="status" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if($orders->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-indigo-600">#{{ $order->order_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $order->user?->name ?? 'Guest' }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">${{ number_format($order->total, 2) }}</td>
                                    <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->payment_status_badge }}">{{ ucfirst($order->payment_status) }}</span></td>
                                    <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->status_badge }}">{{ ucfirst($order->status) }}</span></td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">{{ $orders->links() }}</div>
            @else
                <div class="px-6 py-12 text-center"><p class="text-gray-500">No orders found</p></div>
            @endif
        </div>
    </div>
</x-admin.layout>
