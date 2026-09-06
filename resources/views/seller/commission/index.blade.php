<x-seller.layout title="Commission History" active="commission">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Commission History</h2>
        <p class="text-sm text-gray-500 mt-1">Track commissions deducted from your orders</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['label' => 'Total Order Value', 'value' => '$' . number_format($stats['total_commission'] + $stats['total_earnings'], 2), 'color' => 'gray'],
            ['label' => 'Total Commission', 'value' => '$' . number_format($stats['total_commission'], 2), 'color' => 'red'],
            ['label' => 'Your Earnings', 'value' => '$' . number_format($stats['total_earnings'], 2), 'color' => 'green'],
            ['label' => 'Pending', 'value' => '$' . number_format($stats['pending_commission'], 2), 'color' => 'yellow'],
            ['label' => 'Settled', 'value' => '$' . number_format($stats['settled_commission'], 2), 'color' => 'blue'],
        ] as $stat)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                <p class="text-lg font-bold text-gray-900 mt-1">{{ $stat['value'] }}</p>
            </div>
        @endforeach
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
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
            @if(request()->hasAny(['status', 'date_from', 'date_to']))
                <a href="{{ route('seller.commission.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($records->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Your Earnings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($records as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-600">#{{ $record->order?->order_number ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">${{ $record->formatted_order_amount }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-medium">-${{ $record->formatted_commission }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold">${{ $record->formatted_earnings }}</td>
                                <td class="px-6 py-4 text-xs text-gray-500">{{ $record->commissionRule?->name ?? 'Default' }} ({{ $record->commission_value }}{{ $record->commission_type === 'percentage' ? '%' : '' }})</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $record->status_badge }}">{{ ucfirst($record->status) }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $record->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $records->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <p class="text-gray-500">No commission records found</p>
            </div>
        @endif
    </div>
</x-seller.layout>
