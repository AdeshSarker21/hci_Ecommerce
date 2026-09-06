<x-admin.layout title="{{ $seller->store_name }}" active="sellers">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                    <img src="{{ $seller->logo_url }}" class="w-16 h-16 rounded-xl">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $seller->store_name }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $seller->user?->name }} &middot; {{ $seller->user?->email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $seller->status_badge }}">{{ ucfirst($seller->status) }}</span>
                            @if($seller->is_featured)
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Featured</span>
                            @endif
                            <span class="text-xs text-gray-500">Member since {{ $seller->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($seller->isPending())
                        <form method="POST" action="{{ route('admin.sellers.approve', $seller) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Approve</button>
                        </form>
                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Reject</button>
                    @elseif($seller->isApproved())
                        <button onclick="document.getElementById('suspend-modal').classList.remove('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">Suspend</button>
                    @elseif($seller->isSuspended() || $seller->isRejected())
                        <form method="POST" action="{{ route('admin.sellers.activate', $seller) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Reactivate</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.sellers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Back to List</a>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Total Products</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['products_count'] }}</p>
                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                    <span class="text-green-600">{{ $stats['published_products'] }} published</span>
                    <span>&middot;</span>
                    <span class="text-yellow-600">{{ $stats['pending_products'] }} pending</span>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['orders_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Total Sales</p>
                <p class="text-2xl font-bold text-green-600 mt-1">${{ number_format($stats['total_sales'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500">Commission Earned</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">${{ number_format($stats['total_commission'], 2) }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ activeTab: 'overview' }">
            <div class="bg-white rounded-xl border border-gray-200 p-1 flex flex-wrap gap-1">
                @foreach(['overview' => 'Overview', 'products' => 'Products', 'orders' => 'Orders', 'finance' => 'Finance', 'activity' => 'Activity'] as $tab => $label)
                    <button @click="activeTab = '{{ $tab }}'"
                            :class="activeTab === '{{ $tab }}' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors">{{ $label }}</button>
                @endforeach
            </div>

            {{-- Overview --}}
            <div x-show="activeTab === 'overview'" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Store Info --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Store Information</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Store Name</dt><dd class="font-medium text-gray-900">{{ $seller->store_name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Slug</dt><dd class="font-medium text-gray-900">{{ $seller->store_slug }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $seller->contact_phone ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $seller->contact_email ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Website</dt><dd class="font-medium text-gray-900">{{ $seller->contact_website ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900 text-right max-w-[200px]">{{ $seller->full_address }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Commission Rate</dt><dd class="font-medium text-gray-900">{{ $seller->commission_rate }}%</dd></div>
                    </dl>
                </div>

                {{-- Seller Info --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Seller Information</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Name</dt><dd class="font-medium text-gray-900">{{ $seller->user?->name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $seller->user?->email }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Staff Members</dt><dd class="font-medium text-gray-900">{{ $seller->staffDetails->count() }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Registered</dt><dd class="font-medium text-gray-900">{{ $seller->created_at->format('M d, Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Approved</dt><dd class="font-medium text-gray-900">{{ $seller->approved_at?->format('M d, Y') ?? '-' }}</dd></div>
                    </dl>
                    @if($seller->rejection_reason)
                        <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-xs font-medium text-red-800">Rejection/Suspension Reason:</p>
                            <p class="text-sm text-red-700 mt-1">{{ $seller->rejection_reason }}</p>
                        </div>
                    @endif
                </div>

                {{-- Wallet Summary --}}
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Wallet Summary</h3>
                    @if($seller->wallet)
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Pending Balance</dt><dd class="font-medium text-yellow-600">${{ number_format($seller->wallet->pending_balance, 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Available Balance</dt><dd class="font-medium text-green-600">${{ number_format($seller->wallet->available_balance, 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Withdrawn</dt><dd class="font-medium text-gray-900">${{ number_format($seller->wallet->withdrawn_amount, 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Total Earned</dt><dd class="font-bold text-gray-900">${{ number_format($seller->wallet->total_earned, 2) }}</dd></div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-500">No wallet configured</p>
                    @endif
                </div>
            </div>

            {{-- Products --}}
            <div x-show="activeTab === 'products'" class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Products ({{ $stats['products_count'] }})</h3>
                        <a href="{{ route('admin.sellers.products', $seller) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View All</a>
                    </div>
                    @if($recentProducts->count())
                        <div class="divide-y divide-gray-100">
                            @foreach($recentProducts as $product)
                                <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                            <p class="text-xs text-gray-500">${{ number_format($product->price, 2) }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        @if($product->status === 'published') bg-green-100 text-green-800
                                        @elseif($product->status === 'pending_review') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-8 text-center"><p class="text-gray-500">No products yet</p></div>
                    @endif
                </div>
            </div>

            {{-- Orders --}}
            <div x-show="activeTab === 'orders'" class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Orders ({{ $stats['orders_count'] }})</h3>
                        <a href="{{ route('admin.sellers.orders', $seller) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View All</a>
                    </div>
                    @if($recentOrders->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentOrders as $order)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-3 text-sm font-medium text-indigo-600">#{{ $order->order_number }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ $order->user?->name ?? 'Guest' }}</td>
                                            <td class="px-6 py-3 text-sm font-medium text-gray-900">${{ number_format($order->total, 2) }}</td>
                                            <td class="px-6 py-3"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $order->status_badge }}">{{ ucfirst($order->status) }}</span></td>
                                            <td class="px-6 py-3 text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-8 text-center"><p class="text-gray-500">No orders yet</p></div>
                    @endif
                </div>
            </div>

            {{-- Finance --}}
            <div x-show="activeTab === 'finance'" class="mt-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Pending Commission</p>
                        <p class="text-xl font-bold text-yellow-600 mt-1">${{ number_format($stats['pending_commission'], 2) }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Settled Commission</p>
                        <p class="text-xl font-bold text-green-600 mt-1">${{ number_format($stats['settled_commission'], 2) }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Wallet Balance</p>
                        <p class="text-xl font-bold text-indigo-600 mt-1">${{ number_format(($seller->wallet?->available_balance ?? 0) + ($seller->wallet?->pending_balance ?? 0), 2) }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <p class="text-xs font-medium text-gray-500">Total Withdrawn</p>
                        <p class="text-xl font-bold text-gray-900 mt-1">${{ number_format($seller->wallet?->withdrawn_amount ?? 0, 2) }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.sellers.finance', $seller) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    View Full Finance Details
                    <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Activity --}}
            <div x-show="activeTab === 'activity'" class="mt-6">
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Activity Log</h3>
                    </div>
                    @if($recentActivity->count())
                        <div class="divide-y divide-gray-100">
                            @foreach($recentActivity as $log)
                                <div class="px-6 py-4 flex items-start space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">{{ $log->description }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-500">{{ $log->user?->name ?? 'System' }}</span>
                                            <span class="text-xs text-gray-400">&middot;</span>
                                            <span class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ $log->type }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-8 text-center"><p class="text-gray-500">No activity recorded yet</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    @if($seller->isPending())
    <div id="reject-modal" class="hidden fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Seller Application</h3>
            <form method="POST" action="{{ route('admin.sellers.reject', $seller) }}">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason (required)</label>
                <textarea name="rejection_reason" rows="3" required maxlength="500" class="w-full text-sm border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="Explain why..."></textarea>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Suspend Modal --}}
    @if($seller->isApproved())
    <div id="suspend-modal" class="hidden fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Suspend Seller</h3>
            <form method="POST" action="{{ route('admin.sellers.suspend', $seller) }}">
                @csrf
                <label class="block text-sm font-medium text-gray-700 mb-2">Suspension Reason (required)</label>
                <textarea name="rejection_reason" rows="3" required maxlength="500" class="w-full text-sm border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500" placeholder="Explain why..."></textarea>
                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" onclick="document.getElementById('suspend-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">Suspend</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-admin.layout>
