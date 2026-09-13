<x-admin.layout title="Collection Detail" active="courier-collections">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.courier-collections.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Collection #{{ $collection->consignment_id ?? $collection->id }}</h2>
                <p class="text-sm text-gray-500 mt-1">Courier collection detail and reconciliation</p>
            </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Order & Seller</h4>
            <dl class="space-y-2">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Order</dt><dd class="text-sm font-medium text-gray-900">{{ $collection->order?->order_number ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Seller</dt><dd class="text-sm font-medium text-gray-900">{{ $collection->seller?->store_name ?? 'N/A' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Courier</dt><dd class="text-sm font-medium text-gray-900">{{ $collection->courier?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Consignment ID</dt><dd class="text-sm font-mono text-gray-900">{{ $collection->consignment_id ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Order Created</dt><dd class="text-sm text-gray-900">{{ $collection->order?->created_at?->format('M d, Y H:i') ?? '-' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Financial Summary</h4>
            <dl class="space-y-2">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">COD Amount</dt><dd class="text-sm font-bold text-gray-900">${{ number_format($collection->cod_amount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Courier Charge</dt><dd class="text-sm font-medium text-red-600">${{ number_format($collection->courier_charge, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Net Collected</dt><dd class="text-sm font-bold text-emerald-600">${{ number_format($collection->net_collected, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Commission</dt><dd class="text-sm font-medium text-orange-600">${{ number_format($collection->commission_amount, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Seller Earning</dt><dd class="text-sm font-bold text-indigo-600">${{ number_format($collection->seller_earning, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Marketplace Earning</dt><dd class="text-sm font-medium text-gray-900">${{ number_format($collection->marketplace_earning, 2) }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Status</h4>
            <dl class="space-y-3">
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-500">Collection</dt>
                    <dd><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $collection->status_badge }}">{{ ucfirst(str_replace('_', ' ', $collection->collection_status)) }}</span></dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-500">Settlement</dt>
                    <dd><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $collection->settlement_status_badge }}">{{ ucfirst(str_replace('_', ' ', $collection->settlement_status)) }}</span></dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-sm text-gray-500">Reconciliation</dt>
                    <dd><span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $collection->reconciliation_status_badge }}">{{ ucfirst(str_replace('_', ' ', $collection->reconciliation_status)) }}</span></dd>
                </div>
                @if($collection->discrepancy_amount)
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                        <p class="text-xs font-semibold text-orange-700">Discrepancy: ${{ number_format($collection->discrepancy_amount, 2) }}</p>
                        @if($collection->discrepancy_notes)
                            <p class="text-xs text-orange-600 mt-1">{{ $collection->discrepancy_notes }}</p>
                        @endif
                    </div>
                @endif
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Delivery Date</dt><dd class="text-sm text-gray-900">{{ $collection->delivery_date?->format('M d, Y') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Courier Payment</dt><dd class="text-sm text-gray-900">{{ $collection->courier_payment_date?->format('M d, Y') ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Payment Ref</dt><dd class="text-sm font-mono text-gray-900">{{ $collection->courier_payment_reference ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Reconciled By</dt><dd class="text-sm text-gray-900">{{ $collection->reconciledByUser?->name ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>

    @if($collection->notes || $collection->admin_notes)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Notes</h4>
            @if($collection->notes)
                <p class="text-sm text-gray-700 mb-2"><span class="font-medium">System:</span> {{ $collection->notes }}</p>
            @endif
            @if($collection->admin_notes)
                <p class="text-sm text-gray-700"><span class="font-medium">Admin:</span> {{ $collection->admin_notes }}</p>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        @if($collection->canBeReconciled())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Manual Reconciliation</h4>
            <form method="POST" action="{{ route('admin.courier-collections.reconcile', $collection) }}">
                @csrf @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Courier Charge (BDT)</label>
                        <input type="number" step="0.01" name="courier_charge" value="{{ $collection->courier_charge }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Notes</label>
                        <textarea name="admin_notes" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Reason for manual reconciliation..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discrepancy Notes</label>
                        <textarea name="discrepancy_notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Explain any discrepancy..."></textarea>
                    </div>
                </div>
                <button type="submit" class="mt-4 px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700">Reconcile Manually</button>
            </form>
        </div>
        @endif

        @if($collection->collection_status === 'pending')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Actions</h4>
            <div class="space-y-3">
                <form method="POST" action="{{ route('admin.courier-collections.confirm', $collection) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700">Confirm Collection</button>
                </form>
                @if($collection->courier_id)
                <form method="POST" action="{{ route('admin.courier-collections.sync-single', $collection) }}">
                    @csrf
                    <button type="submit" class="w-full px-5 py-2.5 text-sm font-semibold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100">Sync from Courier API</button>
                </form>
                @endif
            </div>
        </div>
        @endif

        @if($collection->settlement_status !== 'settled')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Update Settlement Status</h4>
            <form method="POST" action="{{ route('admin.courier-collections.update-settlement-status', $collection) }}">
                @csrf @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Settlement Status</label>
                        <select name="settlement_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            <option value="pending" {{ $collection->settlement_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="settled" {{ $collection->settlement_status === 'settled' ? 'selected' : '' }}>Settled</option>
                            <option value="failed" {{ $collection->settlement_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"></textarea>
                    </div>
                </div>
                <button type="submit" class="mt-4 px-5 py-2 text-sm font-semibold text-white bg-gray-800 rounded-xl hover:bg-gray-900">Update Status</button>
            </form>
        </div>
        @endif
    </div>

    @if($collection->raw_courier_data)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Raw Courier Data</h4>
            <pre class="bg-gray-50 rounded-lg p-4 text-xs text-gray-700 overflow-x-auto max-h-64">{{ json_encode($collection->raw_courier_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    @endif
</x-admin.layout>
