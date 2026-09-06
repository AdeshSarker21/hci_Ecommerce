<x-admin.layout title="All Sellers" active="sellers">
    <div class="space-y-6">
        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="{{ route('admin.sellers.index') }}" class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow {{ !request('status') ? 'ring-2 ring-indigo-500' : '' }}">
                <p class="text-xs font-medium text-gray-500">Total Sellers</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            </a>
            <a href="{{ route('admin.sellers.index', ['status' => 'pending']) }}" class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow {{ request('status') === 'pending' ? 'ring-2 ring-yellow-500' : '' }}">
                <p class="text-xs font-medium text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] }}</p>
            </a>
            <a href="{{ route('admin.sellers.index', ['status' => 'approved']) }}" class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow {{ request('status') === 'approved' ? 'ring-2 ring-green-500' : '' }}">
                <p class="text-xs font-medium text-gray-500">Active</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['approved'] }}</p>
            </a>
            <a href="{{ route('admin.sellers.index', ['status' => 'rejected']) }}" class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow {{ request('status') === 'rejected' ? 'ring-2 ring-red-500' : '' }}">
                <p class="text-xs font-medium text-gray-500">Rejected</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</p>
            </a>
            <a href="{{ route('admin.sellers.index', ['status' => 'suspended']) }}" class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow {{ request('status') === 'suspended' ? 'ring-2 ring-orange-500' : '' }}">
                <p class="text-xs font-medium text-gray-500">Suspended</p>
                <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['suspended'] }}</p>
            </a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Store name, email, phone..."
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Sort</label>
                    <select name="sort" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                        <option value="products" {{ request('sort') === 'products' ? 'selected' : '' }}>Most Products</option>
                        <option value="orders" {{ request('sort') === 'orders' ? 'selected' : '' }}>Most Orders</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Filter</button>
                @if(request('search') || request('status') || request('sort'))
                    <a href="{{ route('admin.sellers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if($sellers->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($sellers as $seller)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img src="{{ $seller->user?->avatar_url }}" class="w-8 h-8 rounded-full mr-3">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $seller->user?->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $seller->user?->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $seller->store_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $seller->store_slug }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $seller->status_badge }}">{{ ucfirst($seller->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $seller->products_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $seller->orders_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $seller->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('admin.sellers.show', $seller) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">{{ $sellers->links() }}</div>
            @else
                <div class="px-6 py-16 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <p class="text-gray-500">No sellers found</p>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
