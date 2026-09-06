<x-admin.layout title="Seller Performance" active="sellers-performance">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Seller Performance</h2>
                <p class="text-sm text-gray-500 mt-1">Compare sales, orders and earnings across active sellers</p>
            </div>
        </div>

        {{-- Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex items-end gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sellers..."
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Search</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if($sellers->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Products</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sales</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Order</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Commission</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wallet</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($sellers as $seller)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img src="{{ $seller->user?->avatar_url }}" class="w-8 h-8 rounded-full mr-3">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $seller->store_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $seller->user?->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $seller->products_count }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $seller->orders_count }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-green-600">${{ number_format($seller->total_sales, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-900">${{ number_format($seller->avg_order_value, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-indigo-600">${{ number_format($seller->total_commission, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-right">
                                        <span class="text-green-600">${{ number_format($seller->available_balance, 2) }}</span>
                                        @if($seller->pending_balance > 0)
                                            <span class="text-yellow-600 text-xs"> (+${{ number_format($seller->pending_balance, 2) }} pending)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.sellers.show', $seller) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">No active sellers found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-gray-500">No sellers match your search</p>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
