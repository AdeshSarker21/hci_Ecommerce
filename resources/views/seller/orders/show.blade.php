<x-seller.layout title="Order #{{ $order->order_number }}" active="orders">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('seller.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">#{{ $order->order_number }}</h2>
                <p class="text-sm text-gray-500">Placed {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $order->status_badge }}">{{ ucfirst($order->status) }}</span>
            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $order->payment_status_badge }}">{{ ucfirst($order->payment_status) }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Order Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product_name }}</p>
                                <p class="text-xs text-gray-400">SKU: {{ $item->product_sku ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-gray-900">${{ $item->formatted_total }}</p>
                                <p class="text-xs text-gray-400">{{ $item->quantity }} x ${{ $item->formatted_unit_price }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="text-gray-900">${{ number_format($order->subtotal, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Tax</span><span class="text-gray-900">${{ number_format($order->tax, 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Shipping</span><span class="text-gray-900">${{ number_format($order->shipping_cost, 2) }}</span></div>
                        @if($order->discount > 0)
                            <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="text-red-600">-${{ number_format($order->discount, 2) }}</span></div>
                        @endif
                        <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold text-base">
                            <span class="text-gray-900">Total</span>
                            <span class="text-gray-900">${{ $order->formatted_total }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shipping Address --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h3>
                @if($order->shipping_address)
                    <div class="text-sm text-gray-600 space-y-1">
                        <p class="font-medium text-gray-900">{{ $order->shipping_address['name'] ?? '' }}</p>
                        <p>{{ $order->shipping_address['address'] ?? '' }}</p>
                        <p>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['zip'] ?? '' }}</p>
                        <p>{{ $order->shipping_address['country'] ?? '' }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-400">No shipping address provided</p>
                @endif
            </div>

            {{-- Status Timeline --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Timeline</h3>
                @if($order->statusHistory->count() > 0)
                    <div class="space-y-4">
                        @foreach($order->statusHistory as $history)
                            <div class="flex gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                        bg-{{ $history->status_color }}-100">
                                        @if($history->type === 'status')
                                            <svg class="w-4 h-4 text-{{ $history->status_color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @elseif($history->type === 'shipment')
                                            <svg class="w-4 h-4 text-{{ $history->status_color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-{{ $history->status_color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <div class="w-0.5 flex-1 bg-gray-200 mt-1"></div>
                                    @endif
                                </div>
                                <div class="pb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $history->type_badge }}">
                                            {{ ucfirst($history->type) }}
                                        </span>
                                        @if($history->from_status)
                                            <span class="text-xs text-gray-400">{{ ucfirst($history->from_status) }} &rarr;</span>
                                        @endif
                                        <span class="text-sm font-medium text-gray-900">{{ ucfirst($history->to_status) }}</span>
                                    </div>
                                    @if($history->note)
                                        <p class="text-sm text-gray-600 mt-1">{{ $history->note }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $history->user?->name ?? 'System' }} &middot; {{ $history->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400">No status history yet</p>
                @endif
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">

            {{-- Customer Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer</h3>
                <div class="flex items-center gap-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($order->customer_name) }}&background=6366f1&color=fff" class="w-10 h-10 rounded-full">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</p>
                        <p class="text-xs text-gray-400">{{ $order->user?->email }}</p>
                    </div>
                </div>
                @if($order->user?->phone)
                    <p class="text-sm text-gray-600">Phone: {{ $order->user->phone }}</p>
                @endif
            </div>

            {{-- Payment Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Method</span>
                        <span class="text-gray-900 font-medium">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->payment_status_badge }}">{{ ucfirst($order->payment_status) }}</span>
                    </div>
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Paid at</span>
                            <span class="text-gray-900">{{ $order->paid_at->format('M d, Y h:i A') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="text-gray-900 font-bold text-base">${{ $order->formatted_total }}</span>
                    </div>
                </div>
            </div>

            {{-- Shipment Tracking --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipment</h3>
                @if($order->tracking_number)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Courier</span>
                            <span class="text-gray-900 font-medium">{{ $order->courier_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tracking #</span>
                            <span class="text-gray-900 font-mono">{{ $order->tracking_number }}</span>
                        </div>
                        @if($order->tracking_url)
                            <a href="{{ $order->tracking_url }}" target="_blank" class="inline-flex items-center text-emerald-600 hover:text-emerald-700 font-medium">
                                Track Package
                                <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-400 mb-3">No tracking information yet</p>
                @endif

                {{-- Update Tracking Form --}}
                <form method="POST" action="{{ route('seller.orders.tracking', $order) }}" class="mt-4 pt-4 border-t border-gray-100">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-3">
                        <input type="text" name="courier_name" value="{{ $order->courier_name }}" placeholder="Courier name"
                               class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="Tracking number"
                               class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="url" name="tracking_url" value="{{ $order->tracking_url }}" placeholder="Tracking URL (optional)"
                               class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                            Update Tracking
                        </button>
                    </div>
                </form>
            </div>

            {{-- Update Status --}}
            @if(count($allowedTransitions) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h3>
                    <form method="POST" action="{{ route('seller.orders.update-status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-3">
                            <select name="status" class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500" required>
                                <option value="">Select new status...</option>
                                @foreach($allowedTransitions as $transition)
                                    <option value="{{ $transition }}">{{ ucfirst($transition) }}</option>
                                @endforeach
                            </select>
                            <textarea name="note" rows="2" placeholder="Note (optional)"
                                      class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors"
                                    onclick="return confirm('Are you sure you want to update this order status?')">
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Seller Notes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Seller Notes</h3>
                <form method="POST" action="{{ route('seller.orders.note', $order) }}">
                    @csrf
                    @method('PATCH')
                    <textarea name="seller_notes" rows="3" placeholder="Add private notes about this order..."
                              class="w-full text-sm border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">{{ $order->seller_notes }}</textarea>
                    <button type="submit" class="mt-3 px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition-colors">
                        Save Note
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-seller.layout>
