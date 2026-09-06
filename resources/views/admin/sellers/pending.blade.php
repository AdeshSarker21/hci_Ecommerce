<x-admin.layout title="Pending Approvals" active="sellers-approvals">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Seller Registration Requests</h2>
                <p class="text-sm text-gray-500 mt-1">Review and approve new seller applications</p>
            </div>
            <span class="px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800 rounded-full">{{ $sellers->total() }} pending</span>
        </div>

        {{-- Search --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex items-end gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by store name, email..."
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Search</button>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($sellers as $seller)
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <img src="{{ $seller->user?->avatar_url }}" class="w-12 h-12 rounded-full">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $seller->store_name }}</h3>
                                <p class="text-sm text-gray-500">{{ $seller->user?->name }} &middot; {{ $seller->user?->email }}</p>
                                @if($seller->contact_phone)
                                    <p class="text-sm text-gray-500">{{ $seller->contact_phone }}</p>
                                @endif
                                <div class="flex items-center gap-3 mt-2">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $seller->status_badge }}">{{ ucfirst($seller->status) }}</span>
                                    <span class="text-xs text-gray-500">Applied {{ $seller->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.sellers.show', $seller) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View Details</a>
                    </div>

                    @if($seller->store_description)
                        <p class="mt-4 text-sm text-gray-600 line-clamp-2">{{ $seller->store_description }}</p>
                    @endif

                    <div class="flex items-center gap-2 mt-4">
                        @if($seller->business_address)
                            <span class="text-xs text-gray-500">{{ $seller->business_address }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('admin.sellers.approve', $seller) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Approve</button>
                        </form>
                        <button onclick="document.getElementById('reject-{{ $seller->id }}').classList.toggle('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Reject</button>
                    </div>

                    {{-- Rejection Form (hidden) --}}
                    <div id="reject-{{ $seller->id }}" class="hidden mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
                        <form method="POST" action="{{ route('admin.sellers.reject', $seller) }}">
                            @csrf
                            <label class="block text-sm font-medium text-red-800 mb-2">Rejection Reason (required)</label>
                            <textarea name="rejection_reason" rows="3" required maxlength="500" placeholder="Explain why this seller application is being rejected..."
                                      class="w-full text-sm border-red-300 rounded-lg focus:ring-red-500 focus:border-red-500"></textarea>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Confirm Rejection</button>
                                <button type="button" onclick="document.getElementById('reject-{{ $seller->id }}').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 font-medium">No pending approvals</p>
                    <p class="text-sm text-gray-400 mt-1">All seller applications have been reviewed</p>
                </div>
            @endforelse
        </div>

        @if($sellers->hasPages())
            <div class="bg-white rounded-xl border border-gray-200 px-6 py-4">{{ $sellers->links() }}</div>
        @endif
    </div>
</x-admin.layout>
