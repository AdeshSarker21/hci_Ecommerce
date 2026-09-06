<x-admin.layout title="Commission Records" active="commission">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Commission Records</h2>
        <p class="text-sm text-gray-500 mt-1">All commission calculations across orders</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>Settled</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Seller</label>
                <select name="seller_id" class="text-sm border border-gray-300 rounded-lg">
                    <option value="">All Sellers</option>
                    @foreach($sellers as $s)
                        <option value="{{ $s->id }}" {{ request('seller_id') == $s->id ? 'selected' : '' }}>{{ $s->store_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($records->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller Earnings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($records as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-600">#{{ $record->order?->order_number ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $record->seller?->store_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($record->order_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-medium">${{ number_format($record->commission_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold">${{ number_format($record->seller_earnings, 2) }}</td>
                                <td class="px-6 py-4 text-xs text-gray-500">{{ $record->commissionRule?->name ?? 'Default' }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $record->status_badge }}">{{ ucfirst($record->status) }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $record->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $records->links() }}</div>
        @else
            <div class="px-6 py-16 text-center"><p class="text-gray-500">No commission records found</p></div>
        @endif
    </div>
</x-admin.layout>
