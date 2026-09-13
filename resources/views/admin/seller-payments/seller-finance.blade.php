<x-admin.layout title="Finance - {{ $seller->store_name }}" active="seller-payments">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.seller-payments.index') }}" class="hover:text-indigo-600">Seller Payments</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">{{ $seller->store_name }}</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ $seller->store_name }} - Finance Overview</h2>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Commission</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($stats['total_commission'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Earnings</p>
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($stats['total_earnings'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Pending</p>
            <p class="text-2xl font-bold text-amber-600">${{ number_format($stats['pending_commission'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Available</p>
            <p class="text-2xl font-bold text-blue-600">${{ number_format($wallet?->available_balance ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Settled</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_settled'], 2) }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: 'commissions' }" class="space-y-6">
        <div class="flex gap-1 bg-gray-100 rounded-lg p-1 max-w-md">
            <button @click="activeTab = 'commissions'" :class="activeTab === 'commissions' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-600'" class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all">Commissions</button>
            <button @click="activeTab = 'wallet'" :class="activeTab === 'wallet' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-600'" class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all">Wallet</button>
            <button @click="activeTab = 'settlements'" :class="activeTab === 'settlements' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-600'" class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all">Settlements</button>
        </div>

        {{-- Commissions Tab --}}
        <div x-show="activeTab === 'commissions'" x-cloak>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                @if($commissionRecords->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earnings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rule</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($commissionRecords as $record)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-indigo-600">{{ $record->order?->order_number ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($record->order_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-red-600">${{ number_format($record->commission_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-emerald-600 font-medium">${{ number_format($record->seller_earnings, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $record->commissionRule?->name ?? 'Default' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ match($record->status) { 'pending' => 'bg-amber-100 text-amber-700', 'settled' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' } }}">{{ ucfirst($record->status) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $record->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t">{{ $commissionRecords->links() }}</div>
                @else
                    <div class="px-6 py-12 text-center"><p class="text-gray-500 text-sm">No commission records</p></div>
                @endif
            </div>
        </div>

        {{-- Wallet Tab --}}
        <div x-show="activeTab === 'wallet'" x-cloak>
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                    <p class="text-xs text-amber-600 font-medium">Pending</p>
                    <p class="text-xl font-bold text-amber-700">${{ number_format($wallet?->pending_balance ?? 0, 2) }}</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                    <p class="text-xs text-blue-600 font-medium">Available</p>
                    <p class="text-xl font-bold text-blue-700">${{ number_format($wallet?->available_balance ?? 0, 2) }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4 border border-green-100">
                    <p class="text-xs text-green-600 font-medium">Withdrawn</p>
                    <p class="text-xl font-bold text-green-700">${{ number_format($wallet?->withdrawn_amount ?? 0, 2) }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-600 font-medium">Total Earned</p>
                    <p class="text-xl font-bold text-gray-900">${{ number_format($wallet?->total_earned ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                @if($wallet && $wallet->transactions->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance Before</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($wallet->transactions as $txn)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $txn->type_badge }}">{{ $txn->type_label }}</span></td>
                                        <td class="px-6 py-4 text-sm font-semibold {{ $txn->is_credit ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->is_credit ? '+' : '-' }}${{ number_format($txn->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($txn->balance_before, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($txn->balance_after, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $txn->description }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center"><p class="text-gray-500 text-sm">No wallet transactions</p></div>
                @endif
            </div>
        </div>

        {{-- Settlements Tab --}}
        <div x-show="activeTab === 'settlements'" x-cloak>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                @if($settlements->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settlement #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($settlements as $settlement)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-emerald-600">#{{ $settlement->settlement_number }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($settlement->amount, 2) }}</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $settlement->status_badge }}">{{ ucfirst($settlement->status) }}</span></td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $settlement->payment_method ?? 'N/A')) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->reference_number ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t">{{ $settlements->links() }}</div>
                @else
                    <div class="px-6 py-12 text-center"><p class="text-gray-500 text-sm">No settlements</p></div>
                @endif
            </div>
        </div>
    </div>
</x-admin.layout>
