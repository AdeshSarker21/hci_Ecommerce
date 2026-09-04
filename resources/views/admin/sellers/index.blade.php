<x-admin.layout title="Sellers" active="vendors">
    <x-admin.page-header title="Sellers">
        <x-slot:subtitle>Manage vendor/seller applications and accounts</x-slot:subtitle>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <x-admin.card>
        <form method="GET" action="{{ route('admin.sellers.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by store name or owner..."
                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <x-admin.button type="submit" size="md">Filter</x-admin.button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sellers as $seller)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ $seller->logo_url }}" alt="{{ $seller->store_name }}" class="w-10 h-10 rounded-lg">
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $seller->store_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $seller->store_slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $seller->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $seller->status_badge }}">
                                    {{ ucfirst($seller->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $seller->commission_rate }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $seller->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.sellers.show', $seller) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                @if($seller->isPending())
                                    <form method="POST" action="{{ route('admin.sellers.approve', $seller) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No sellers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $sellers->links() }}
        </div>
    </x-admin.card>
</x-admin.layout>
