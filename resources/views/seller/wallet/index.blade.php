<x-seller.layout title="Wallet" active="wallet">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Wallet</h2>
        <p class="text-sm text-gray-500 mt-1">View your transaction history and balance</p>
    </div>

    {{-- Balance Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
            <p class="text-sm opacity-80">Pending</p>
            <p class="text-3xl font-bold mt-1">${{ $balance['pending'] > 0 ? number_format($balance['pending'], 2) : '0.00' }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white">
            <p class="text-sm opacity-80">Available</p>
            <p class="text-3xl font-bold mt-1">${{ $balance['available'] > 0 ? number_format($balance['available'], 2) : '0.00' }}</p>
        </div>
        <div class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-6 text-white">
            <p class="text-sm opacity-80">Withdrawn</p>
            <p class="text-3xl font-bold mt-1">${{ $balance['withdrawn'] > 0 ? number_format($balance['withdrawn'], 2) : '0.00' }}</p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500">Total Credits</p>
            <p class="text-lg font-bold text-green-600">+${{ number_format($summary['total_credits'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500">Total Debits</p>
            <p class="text-lg font-bold text-red-600">-${{ number_format($summary['total_debits'], 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="type" class="text-sm border border-gray-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="commission_credit" {{ request('type') === 'commission_credit' ? 'selected' : '' }}>Commission Earned</option>
                    <option value="settlement_credit" {{ request('type') === 'settlement_credit' ? 'selected' : '' }}>Settlement</option>
                    <option value="withdrawal_debit" {{ request('type') === 'withdrawal_debit' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="adjustment_credit" {{ request('type') === 'adjustment_credit' ? 'selected' : '' }}>Adjustment (+)</option>
                    <option value="adjustment_debit" {{ request('type') === 'adjustment_debit' ? 'selected' : '' }}>Adjustment (-)</option>
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
            @if(request()->hasAny(['type', 'date_from', 'date_to']))
                <a href="{{ route('seller.wallet.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    {{-- Transactions --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($transactions->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($transactions as $tx)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $tx->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $tx->type_badge }}">{{ $tx->type_label }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ $tx->description }}</td>
                                <td class="px-6 py-4 text-sm font-semibold {{ $tx->is_credit ? 'text-green-600' : 'text-red-600' }}">{{ $tx->formatted_amount }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">${{ number_format($tx->balance_after, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $transactions->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <p class="text-gray-500">No transactions yet</p>
            </div>
        @endif
    </div>
</x-seller.layout>
