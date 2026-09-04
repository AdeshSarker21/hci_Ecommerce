<x-admin.layout title="Seller Details" active="vendors">
    <x-admin.page-header title="{{ $seller->store_name }}">
        <x-slot:subtitle>Seller details and management</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.sellers.index') }}" type="secondary">Back to Sellers</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Store Information">
                <div class="flex items-center space-x-6 mb-6">
                    <img src="{{ $seller->logo_url }}" alt="{{ $seller->store_name }}" class="w-20 h-20 rounded-xl">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $seller->store_name }}</h3>
                        <p class="text-gray-500">{{ $seller->store_slug }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $seller->status_badge }}">{{ ucfirst($seller->status) }}</span>
                            @if($seller->is_featured)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Featured</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($seller->store_description)
                    <p class="text-sm text-gray-600 mb-4">{{ $seller->store_description }}</p>
                @endif

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Contact Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $seller->contact_email }}</dd>
                    </div>
                    @if($seller->contact_phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $seller->contact_phone }}</dd>
                        </div>
                    @endif
                    @if($seller->contact_website)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $seller->contact_website }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Commission Rate</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $seller->commission_rate }}%</dd>
                    </div>
                    @if($seller->full_address)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $seller->full_address }}</dd>
                        </div>
                    @endif
                    @if($seller->business_registration_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Registration #</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $seller->business_registration_number }}</dd>
                        </div>
                    @endif
                    @if($seller->tax_id)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tax ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $seller->tax_id }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Applied</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $seller->created_at->format('M d, Y') }}</dd>
                    </div>
                    @if($seller->approved_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Approved</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $seller->approved_at->format('M d, Y') }}</dd>
                        </div>
                    @endif
                </dl>

                @if($seller->rejection_reason)
                    <div class="mt-4 p-3 bg-red-50 rounded-lg">
                        <p class="text-sm font-medium text-red-800">Rejection/Suspension Reason:</p>
                        <p class="text-sm text-red-700 mt-1">{{ $seller->rejection_reason }}</p>
                    </div>
                @endif
            </x-admin.card>

            {{-- Staff --}}
            <x-admin.card title="Staff Members">
                @if($seller->staff->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($seller->staff as $staff)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <img src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" class="w-8 h-8 rounded-full">
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $staff->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $staff->pivot->role }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No staff members.</p>
                @endif
            </x-admin.card>
        </div>

        {{-- Actions --}}
        <div class="space-y-6">
            <x-admin.card title="Actions">
                <div class="space-y-3">
                    @if($seller->isPending())
                        <form method="POST" action="{{ route('admin.sellers.approve', $seller) }}">
                            @csrf
                            <x-admin.button type="submit" size="md" class="w-full justify-center bg-green-600 hover:bg-green-700 text-white">Approve Seller</x-admin.button>
                        </form>

                        <form method="POST" action="{{ route('admin.sellers.reject', $seller) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                            </div>
                            <x-admin.button type="submit" size="md" class="w-full justify-center" type="danger">Reject Seller</x-admin.button>
                        </form>
                    @elseif($seller->isApproved())
                        <form method="POST" action="{{ route('admin.sellers.suspend', $seller) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="suspension_reason" class="block text-sm font-medium text-gray-700 mb-1">Suspension Reason</label>
                                <textarea name="rejection_reason" id="suspension_reason" rows="3" required
                                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                            </div>
                            <x-admin.button type="submit" size="md" class="w-full justify-center" type="danger">Suspend Seller</x-admin.button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500 text-center py-2">No actions available for this status.</p>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card title="Owner">
                <div class="flex items-center p-2">
                    <img src="{{ $seller->user->avatar_url }}" alt="{{ $seller->user->name }}" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $seller->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $seller->user->email }}</p>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
