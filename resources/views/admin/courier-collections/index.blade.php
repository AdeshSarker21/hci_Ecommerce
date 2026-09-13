<x-admin.layout title="Courier Collections & Reconciliation" active="courier-collections">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Courier Collections</h2>
                <p class="text-sm text-gray-500 mt-1">Track COD collections, reconcile with courier statements, and manage settlements</p>
            </div>
            <button onclick="document.getElementById('syncModal').classList.remove('hidden')" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 shadow-sm transition">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Sync from Courier
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_collections'] }}</p>
                    <p class="text-xs text-gray-500">Total Collections</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600">${{ number_format($stats['total_pending_cod'], 2) }}</p>
                    <p class="text-xs text-gray-500">Pending COD</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600">${{ number_format($stats['total_settled_amount'], 2) }}</p>
                    <p class="text-xs text-gray-500">Settled Amount</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['discrepancy'] }}</p>
                    <p class="text-xs text-gray-500">Discrepancies</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Reconciliation Pending</p>
                    <p class="text-xl font-bold text-amber-600">{{ $stats['reconciliation_pending'] }}</p>
                </div>
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Auto-Reconciled</p>
                    <p class="text-xl font-bold text-emerald-600">{{ $stats['reconciled'] }}</p>
                </div>
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Discrepancy</p>
                    <p class="text-xl font-bold text-red-600">${{ number_format($stats['total_discrepancy'], 2) }}</p>
                </div>
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">All Collections</h3>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Consignment ID, order #, seller..."
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Seller</label>
                <select name="seller_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Sellers</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>{{ $seller->store_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Courier</label>
                <select name="courier_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All Couriers</option>
                    @foreach($couriers as $courierItem)
                        <option value="{{ $courierItem->id }}" {{ request('courier_id') == $courierItem->id ? 'selected' : '' }}>{{ $courierItem->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Collection</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Collected</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Settlement</label>
                <select name="settlement_status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All</option>
                    <option value="pending" {{ request('settlement_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="settled" {{ request('settlement_status') === 'settled' ? 'selected' : '' }}>Settled</option>
                    <option value="failed" {{ request('settlement_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Reconciliation</label>
                <select name="reconciliation_status" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">All</option>
                    <option value="pending" {{ request('reconciliation_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="reconciled" {{ request('reconciliation_status') === 'reconciled' ? 'selected' : '' }}>Reconciled</option>
                    <option value="discrepancy" {{ request('reconciliation_status') === 'discrepancy' ? 'selected' : '' }}>Discrepancy</option>
                    <option value="manual_review" {{ request('reconciliation_status') === 'manual_review' ? 'selected' : '' }}>Manual Review</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
            @if(request()->hasAny(['search', 'seller_id', 'courier_id', 'status', 'settlement_status', 'reconciliation_status', 'date_from', 'date_to']))
                <a href="{{ route('admin.courier-collections.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($collections->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consignment ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">COD Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courier Charge</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net Collected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collection</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settlement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reconciliation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($collections as $collection)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-indigo-600">
                                    <a href="{{ route('admin.courier-collections.show', $collection) }}" class="hover:underline">
                                        {{ $collection->order?->order_number ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $collection->seller?->store_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $collection->courier?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $collection->consignment_id ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($collection->cod_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600">${{ number_format($collection->courier_charge, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-emerald-600 font-medium">${{ number_format($collection->net_collected, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $collection->status_badge }}">{{ ucfirst(str_replace('_', ' ', $collection->collection_status)) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $collection->settlement_status_badge }}">{{ ucfirst(str_replace('_', ' ', $collection->settlement_status)) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $collection->reconciliation_status_badge }}">{{ ucfirst(str_replace('_', ' ', $collection->reconciliation_status)) }}</span>
                                    @if($collection->discrepancy_amount)
                                        <span class="block text-xs text-red-500 mt-1">Δ ${{ number_format($collection->discrepancy_amount, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $collection->delivery_date?->format('M d, Y') ?? '-' }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.courier-collections.show', $collection) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">View</a>
                                        @if($collection->collection_status === 'pending')
                                            <form method="POST" action="{{ route('admin.courier-collections.confirm', $collection) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-emerald-600 hover:text-emerald-700 font-medium">Confirm</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $collections->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-gray-500 text-sm">No courier collections found</p>
            </div>
        @endif
    </div>

    <div id="syncModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('syncModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-900">Sync from Courier</h3>
                    <button onclick="document.getElementById('syncModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.courier-collections.sync') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Courier</label>
                            <select name="courier_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                                <option value="">Select courier...</option>
                                @foreach($couriers as $c)
                                    @if($c->supports_payment_sync)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input type="date" name="date_from" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                <input type="date" name="date_to" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">This will fetch cashout statements from the courier API and auto-reconcile matching collections.</p>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('syncModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Sync Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin.layout>
