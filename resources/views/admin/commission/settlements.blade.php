<x-admin.layout title="Settlements" active="commission-settlements">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Settlements</h2>
            <p class="text-sm text-gray-500 mt-1">Manage seller settlements and payout requests</p>
        </div>
        <button onclick="document.getElementById('settlementModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Create Settlement
        </button>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Seller</label>
                <select name="seller_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Sellers</option>
                    @foreach(\App\Models\Seller::approved()->get() as $seller)
                        <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>{{ $seller->store_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
            @if(request()->hasAny(['status', 'seller_id']))
                <a href="{{ route('admin.commission.settlements') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($settlements->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settlement #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($settlements as $settlement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-600">#{{ $settlement->settlement_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $settlement->seller?->store_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($settlement->amount, 2) }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $settlement->status_badge }}">{{ ucfirst($settlement->status) }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $settlement->payment_method ?? 'N/A')) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->reference_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $settlement->notes ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    @if(in_array($settlement->status, ['pending', 'processing']))
                                        <form method="POST" action="{{ route('admin.commission.settlements.complete', $settlement) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-700 font-medium">Complete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $settlements->links() }}</div>
        @else
            <div class="px-6 py-16 text-center"><p class="text-gray-500">No settlements found</p></div>
        @endif
    </div>

    {{-- Create Settlement Modal --}}
    <div id="settlementModal" class="hidden fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true"
         x-data="settlementForm()" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('settlementModal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg bg-white shadow-2xl rounded-2xl">
                <div class="flex items-center justify-between p-6 pb-0">
                    <h3 class="text-lg font-semibold text-gray-900">Create Settlement</h3>
                    <button onclick="document.getElementById('settlementModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.commission.settlements.store') }}" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Seller</label>
                            <select name="seller_id" required
                                    x-model="sellerId"
                                    @change="fetchWallet()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                                <option value="">Select seller...</option>
                                @foreach(\App\Models\Seller::approved()->get() as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->store_name }}</option>
                                @endforeach
                            </select>
                            @error('seller_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Wallet Info --}}
                        <div x-show="loading" class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                            <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span class="text-xs text-gray-500">Loading wallet info...</span>
                        </div>

                        <div x-show="!loading && sellerId" x-transition class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-indigo-900">Seller Wallet</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white rounded-lg p-2.5 border border-indigo-100">
                                    <p class="text-[10px] font-medium text-amber-600 uppercase tracking-wide">Pending Balance</p>
                                    <p class="text-lg font-bold text-amber-700" x-text="'$' + formatNum(wallet.pending)">$0.00</p>
                                </div>
                                <div class="bg-white rounded-lg p-2.5 border border-indigo-100">
                                    <p class="text-[10px] font-medium text-blue-600 uppercase tracking-wide">Available</p>
                                    <p class="text-lg font-bold text-blue-700" x-text="'$' + formatNum(wallet.available)">$0.00</p>
                                </div>
                                <div class="bg-white rounded-lg p-2.5 border border-indigo-100">
                                    <p class="text-[10px] font-medium text-green-600 uppercase tracking-wide">Total Earned</p>
                                    <p class="text-lg font-bold text-green-700" x-text="'$' + formatNum(wallet.totalEarned)">$0.00</p>
                                </div>
                                <div class="bg-white rounded-lg p-2.5 border border-indigo-100">
                                    <p class="text-[10px] font-medium text-gray-600 uppercase tracking-wide">Withdrawn</p>
                                    <p class="text-lg font-bold text-gray-700" x-text="'$' + formatNum(wallet.withdrawn)">$0.00</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="!loading && sellerId && wallet.pending <= 0" class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-sm font-medium text-amber-700">No pending balance available for settlement.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Settlement Amount ($)</label>
                            <div class="relative" x-show="!loading && sellerId">
                                <input type="number" name="amount" step="0.01" min="0.01"
                                       x-model="amount"
                                       :max="wallet.pending"
                                       :disabled="wallet.pending <= 0"
                                       required
                                       class="w-full border rounded-lg px-3 py-2 pr-20 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                                       :class="exceedsMax ? 'border-red-300 bg-red-50' : 'border-gray-300'"
                                       placeholder="0.00">
                                <button type="button"
                                        @click="amount = wallet.pending"
                                        :disabled="wallet.pending <= 0"
                                        x-show="wallet.pending > 0"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-0.5 text-[10px] font-semibold text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 transition-colors disabled:opacity-50">
                                    Pay All
                                </button>
                            </div>
                            <input type="number" name="amount" disabled placeholder="Select a seller first"
                                   x-show="!sellerId"
                                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                            <p x-show="exceedsMax" class="text-xs text-red-500 mt-1">
                                Amount cannot exceed pending balance of <span x-text="'$' + formatNum(wallet.pending)"></span>
                            </p>
                            @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                        <button type="button" onclick="document.getElementById('settlementModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                        <button type="submit"
                                :disabled="!sellerId || loading || exceedsMax || wallet.pending <= 0"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Create Settlement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function settlementForm() {
            return {
                sellerId: '',
                amount: '',
                loading: false,
                wallet: { pending: 0, available: 0, totalEarned: 0, withdrawn: 0 },

                get exceedsMax() {
                    if (!this.amount || !this.sellerId) return false;
                    return parseFloat(this.amount) > this.wallet.pending;
                },

                formatNum(val) {
                    return parseFloat(val || 0).toFixed(2);
                },

                async fetchWallet() {
                    if (!this.sellerId) {
                        this.wallet = { pending: 0, available: 0, totalEarned: 0, withdrawn: 0 };
                        return;
                    }
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route("admin.commission.seller-wallet") }}?seller_id=' + this.sellerId);
                        const data = await res.json();
                        this.wallet = {
                            pending: data.pending_balance,
                            available: data.available_balance,
                            totalEarned: data.total_earned,
                            withdrawn: data.withdrawn_amount,
                        };
                    } catch (e) {
                        console.error('Failed to fetch wallet:', e);
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-admin.layout>
