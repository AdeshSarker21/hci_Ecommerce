<x-admin.layout title="Finance - {{ $seller->store_name }}" active="sellers">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.sellers.show', $seller) }}" class="hover:text-indigo-600">{{ $seller->store_name }}</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-medium">Finance</span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Total Commission</p>
                <p class="text-xl font-bold text-indigo-600 mt-1">${{ number_format($stats['total_commission'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Pending Commission</p>
                <p class="text-xl font-bold text-yellow-600 mt-1">${{ number_format($stats['pending_commission'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Wallet Available</p>
                <p class="text-xl font-bold text-green-600 mt-1">${{ number_format($stats['wallet_available'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Total Withdrawn</p>
                <p class="text-xl font-bold text-gray-900 mt-1">${{ number_format($stats['wallet_withdrawn'], 2) }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ tab: 'commissions' }">
            <div class="bg-white rounded-xl border border-gray-200 p-1 flex flex-wrap gap-1">
                @foreach(['commissions' => 'Commissions', 'wallet' => 'Wallet Transactions', 'settlements' => 'Settlements'] as $key => $label)
                    <button @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">{{ $label }}</button>
                @endforeach
            </div>

            {{-- Commissions --}}
            <div x-show="tab === 'commissions'" class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    @if($commissionRecords->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Order Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Commission</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Seller Earnings</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($commissionRecords as $record)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-indigo-600">#{{ $record->order?->order_number ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900">${{ number_format($record->order_amount, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-red-600">${{ number_format($record->commission_amount, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-green-600 font-medium">${{ number_format($record->seller_earnings, 2) }}</td>
                                            <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $record->status_badge }}">{{ ucfirst($record->status) }}</span></td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $record->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200">{{ $commissionRecords->links() }}</div>
                    @else
                        <div class="px-6 py-12 text-center"><p class="text-gray-500">No commission records</p></div>
                    @endif
                </div>
            </div>

            {{-- Wallet Transactions --}}
            <div x-show="tab === 'wallet'" class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    @if($walletTransactions->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance Before</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance After</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($walletTransactions as $tx)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $tx->type_badge }}">{{ $tx->type_label }}</span></td>
                                            <td class="px-6 py-4 text-sm text-right font-medium {{ $tx->is_credit ? 'text-green-600' : 'text-red-600' }}">{{ $tx->formatted_amount }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-500">${{ number_format($tx->balance_before, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-right text-gray-900">${{ number_format($tx->balance_after, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $tx->description ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $tx->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200">{{ $walletTransactions->links() }}</div>
                    @else
                        <div class="px-6 py-12 text-center"><p class="text-gray-500">No wallet transactions</p></div>
                    @endif
                </div>
            </div>

            {{-- Settlements --}}
            <div x-show="tab === 'settlements'" class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    @if($settlements->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settlement #</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($settlements as $settlement)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-indigo-600">#{{ $settlement->settlement_number }}</td>
                                            <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">${{ number_format($settlement->amount, 2) }}</td>
                                            <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $settlement->status_badge }}">{{ ucfirst($settlement->status) }}</span></td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200">{{ $settlements->links() }}</div>
                    @else
                        <div class="px-6 py-12 text-center"><p class="text-gray-500">No settlements</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
