<x-admin.layout title="Payment Details - {{ $order->order_number }}" active="seller-payments">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="{{ route('admin.seller-payments.index') }}" class="hover:text-indigo-600">Seller Payments</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">{{ $order->order_number }}</span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900">Payment Details</h2>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Order & Payment Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Order Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Order Number</p>
                            <p class="text-sm font-semibold text-indigo-600">{{ $order->order_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->status_badge }}">{{ ucfirst($order->status) }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Payment Status</p>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->payment_status_badge }}">{{ ucfirst($order->payment_status) }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Payment Method</p>
                            <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-4">
                        @if($order->shipment)
                            <a href="{{ route('admin.shipments.show', $order->shipment) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                View Shipment ({{ $order->shipment->shipment_number }})
                            </a>
                        @elseif(in_array($order->status, ['processing', 'shipped']) && in_array($order->payment_method, ['cod', 'prepaid']))
                            <a href="{{ route('admin.shipments.create', $order) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Create Shipment
                            </a>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Seller</p>
                            <a href="{{ route('admin.seller-payments.seller-finance', $order->seller_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ $order->seller?->store_name ?? 'N/A' }}</a>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Customer</p>
                            <p class="text-sm font-medium text-gray-900">{{ $order->user?->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Order Date</p>
                            <p class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Delivery Date</p>
                            <p class="text-sm text-gray-900">{{ $order->delivered_at?->format('M d, Y \a\t h:i A') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Commission Breakdown --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Commission Breakdown</h3>
                </div>
                <div class="p-6">
                    @if($commissionRecord)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                <p class="text-2xl font-bold text-gray-900">${{ number_format($commissionRecord->order_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Order Amount</p>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-xl">
                                <p class="text-2xl font-bold text-red-600">${{ number_format($commissionRecord->commission_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Platform Commission</p>
                            </div>
                            <div class="text-center p-4 bg-emerald-50 rounded-xl">
                                <p class="text-2xl font-bold text-emerald-600">${{ number_format($commissionRecord->seller_earnings, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Seller Earnings</p>
                            </div>
                            <div class="text-center p-4 bg-indigo-50 rounded-xl">
                                <p class="text-2xl font-bold text-indigo-600">{{ $commissionRecord->commission_value }}{{ $commissionRecord->commission_type === 'percentage' ? '%' : '' }}</p>
                                <p class="text-xs text-gray-500 mt-1">Commission Rate</p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-4 text-sm">
                            <span class="text-gray-500">Status:</span>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ match($commissionRecord->status) { 'pending' => 'bg-amber-100 text-amber-700', 'settled' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' } }}">{{ ucfirst($commissionRecord->status) }}</span>
                            @if($commissionRecord->settled_at)
                                <span class="text-gray-500">Settled: {{ $commissionRecord->settled_at->format('M d, Y') }}</span>
                            @endif
                            @if($commissionRecord->commissionRule)
                                <span class="text-gray-500">Rule: {{ $commissionRecord->commissionRule->name }}</span>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No commission record yet</p>
                            @if($order->status === 'delivered' && $order->payment_status === 'paid')
                                <form method="POST" action="{{ route('admin.commission.process') }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Process Commission Now</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Order Items</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($order->items as $item)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($item->product && $item->product->primary_image)
                                    <img src="{{ asset('storage/' . $item->product->primary_image) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover rounded-lg">
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-400">SKU: {{ $item->product_sku ?? 'N/A' }} | Qty: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($item->total, 2) }}</p>
                                <p class="text-xs text-gray-400">${{ number_format($item->unit_price, 2) }} each</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500">No items</div>
                    @endforelse
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-between text-sm">
                    <div class="space-y-1">
                        <p class="text-gray-500">Subtotal: <span class="text-gray-900 font-medium">${{ number_format($order->subtotal, 2) }}</span></p>
                        <p class="text-gray-500">Tax: <span class="text-gray-900 font-medium">${{ number_format($order->tax, 2) }}</span></p>
                        <p class="text-gray-500">Shipping: <span class="text-gray-900 font-medium">${{ number_format($order->shipping_cost, 2) }}</span></p>
                        @if($order->discount > 0)
                            <p class="text-gray-500">Discount: <span class="text-red-600 font-medium">-${{ number_format($order->discount, 2) }}</span></p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-900">${{ number_format($order->total, 2) }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Sidebar Info --}}
        <div class="space-y-6">
            {{-- Settlement Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Settlement</h3>
                </div>
                <div class="p-6">
                    @if($settlement)
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500">Settlement #</p>
                                <p class="text-sm font-medium text-emerald-600">#{{ $settlement->settlement_number }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Amount</p>
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($settlement->amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $settlement->status_badge }}">{{ ucfirst($settlement->status) }}</span>
                            </div>
                            @if($settlement->payment_method)
                                <div>
                                    <p class="text-xs text-gray-500">Payment Method</p>
                                    <p class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $settlement->payment_method)) }}</p>
                                </div>
                            @endif
                            @if($settlement->reference_number)
                                <div>
                                    <p class="text-xs text-gray-500">Reference #</p>
                                    <p class="text-sm text-gray-900">{{ $settlement->reference_number }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500">Created</p>
                                <p class="text-sm text-gray-900">{{ $settlement->created_at->format('M d, Y') }}</p>
                            </div>
                            @if($settlement->completed_at)
                                <div>
                                    <p class="text-xs text-gray-500">Completed</p>
                                    <p class="text-sm text-gray-900">{{ $settlement->completed_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                            @if(in_array($settlement->status, ['pending', 'processing']))
                                <form method="POST" action="{{ route('admin.seller-payments.settlement.complete', $settlement) }}" class="mt-3">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">Mark as Completed</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm text-gray-500">No settlement recorded</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Wallet Activity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Wallet Activity</h3>
                </div>
                <div class="p-6">
                    @if($wallet && $wallet->transactions->count())
                        <div class="space-y-3">
                            @foreach($wallet->transactions->take(5) as $txn)
                                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                    <div>
                                        <p class="text-xs font-medium {{ $txn->is_credit ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->type_label }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $txn->description }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold {{ $txn->is_credit ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->is_credit ? '+' : '-' }}${{ number_format($txn->amount, 2) }}</p>
                                        <p class="text-xs text-gray-400">{{ $txn->created_at->format('M d') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">No wallet transactions</p>
                    @endif
                </div>
            </div>

            {{-- Status History --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-900">Status History</h3>
                </div>
                <div class="p-6">
                    @if($order->statusHistory->count())
                        <div class="space-y-3">
                            @foreach($order->statusHistory->take(10) as $history)
                                <div class="flex gap-3 {{ !$loop->last ? 'pb-3 border-b border-gray-100' : '' }}">
                                    <div class="w-2 h-2 rounded-full bg-indigo-400 mt-1.5 flex-shrink-0"></div>
                                    <div>
                                        <p class="text-sm text-gray-900">
                                            <span class="font-medium">{{ ucfirst($history->from_status ?? 'created') }}</span>
                                            <svg class="w-3 h-3 inline text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <span class="font-medium">{{ ucfirst($history->to_status) }}</span>
                                        </p>
                                        @if($history->note)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $history->note }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $history->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center">No history</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
