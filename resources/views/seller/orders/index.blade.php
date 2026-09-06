<x-seller.layout title="Orders" active="orders">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Orders</h2>
                <p class="text-sm text-gray-500 mt-1">Manage and fulfill your orders</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach([
            'total' => ['label' => 'Total', 'color' => 'gray'],
            'pending' => ['label' => 'Pending', 'color' => 'yellow'],
            'processing' => ['label' => 'Processing', 'color' => 'blue'],
            'shipped' => ['label' => 'Shipped', 'color' => 'indigo'],
            'delivered' => ['label' => 'Delivered', 'color' => 'green'],
            'cancelled' => ['label' => 'Cancelled', 'color' => 'red'],
        ] as $key => $config)
            <a href="{{ route('seller.orders.index', array_merge(request()->query(), ['status' => $key === 'total' ? '' : $key])) }}"
               class="bg-white rounded-xl shadow-sm border {{ request('status') === $key || (!request('status') && $key === 'total') ? 'border-emerald-300 ring-1 ring-emerald-200' : 'border-gray-200' }} p-4 hover:shadow-md transition-all">
                <p class="text-xs font-medium text-gray-500">{{ $config['label'] }}</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats[$key] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" action="{{ route('seller.orders.index') }}" class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search --}}
                <div class="lg:col-span-2">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders, customers, products..."
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                {{-- Payment Status --}}
                <div>
                    <select name="payment_status" class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">All Payments</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
                           class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                {{-- Date To --}}
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
                           class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div class="flex items-center gap-2 mt-3">
                {{-- Sort --}}
                <select name="sort" class="text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="total_high" {{ request('sort') === 'total_high' ? 'selected' : '' }}>Highest Total</option>
                    <option value="total_low" {{ request('sort') === 'total_low' ? 'selected' : '' }}>Lowest Total</option>
                    <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>By Status</option>
                </select>

                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to', 'sort']))
                    <a href="{{ route('seller.orders.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('seller.orders.show', $order) }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->user?->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">
                                        @foreach($order->items->take(2) as $item)
                                            {{ $item->quantity }}x {{ $item->product_name }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                        @if($order->items->count() > 2)
                                            <span class="text-gray-400">+{{ $order->items->count() - 2 }} more</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $order->items->sum('quantity') }} item(s)</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">${{ $order->formatted_total }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->payment_status_badge }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                    <p class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('seller.orders.show', $order) }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
        @else
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 font-medium">No orders found</p>
                <p class="text-sm text-gray-400 mt-1">
                    @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
                        Try adjusting your filters
                    @else
                        Orders will appear here once customers start purchasing
                    @endif
                </p>
            </div>
        @endif
    </div>
</x-seller.layout>
