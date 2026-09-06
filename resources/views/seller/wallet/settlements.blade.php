<x-seller.layout title="Withdrawals" active="wallet">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Settlements & Withdrawals</h2>
        <p class="text-sm text-gray-500 mt-1">Track your settlement history and withdrawal requests</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Total Settled</p>
            <p class="text-2xl font-bold text-green-600 mt-1">${{ number_format($stats['total_settled'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Pending Settlements</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">${{ number_format($stats['pending_settlements'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Available for Withdrawal</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">${{ number_format($balance['available'], 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
        </form>
    </div>

    {{-- Settlements Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($settlements->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settlement #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($settlements as $settlement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-600">#{{ $settlement->settlement_number }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ $settlement->formatted_amount }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $settlement->status_badge }}">{{ ucfirst($settlement->status) }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $settlement->notes ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $settlements->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <p class="text-gray-500">No settlements yet</p>
            </div>
        @endif
    </div>
</x-seller.layout>
