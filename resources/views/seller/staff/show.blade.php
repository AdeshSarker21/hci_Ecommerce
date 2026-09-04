<x-seller.layout title="Staff Details" active="staff">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $staff->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">Staff member details and permissions</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('seller.staff.edit', $staff) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                Edit Staff
            </a>
            <a href="{{ route('seller.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
                Back
            </a>
        </div>
    </div>

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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Staff Information</h3>

                <div class="flex items-center space-x-4 mb-6">
                    @if($staff->user)
                        <img src="{{ $staff->user->avatar_url }}" alt="{{ $staff->name }}" class="w-16 h-16 rounded-full">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                            <span class="text-xl font-medium text-gray-600">{{ strtoupper(substr($staff->name, 0, 2)) }}</span>
                        </div>
                    @endif
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">{{ $staff->name }}</h4>
                        <p class="text-gray-500">{{ $staff->email }}</p>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $staff->status_badge }}">
                            {{ $staff->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($staff->role) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Invited</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->invited_at ? $staff->invited_at->format('M d, Y') : 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Joined</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->joined_at ? $staff->joined_at->format('M d, Y') : 'Pending' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Active</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->last_active_at ? $staff->last_active_at->diffForHumans() : 'Never' }}</dd>
                    </div>
                </dl>

                @if($staff->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700">Notes:</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $staff->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center p-3 border rounded-lg {{ $staff->can_manage_products ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                        <svg class="w-5 h-5 {{ $staff->can_manage_products ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="ml-2 text-sm {{ $staff->can_manage_products ? 'text-emerald-700 font-medium' : 'text-gray-500' }}">Manage Products</span>
                    </div>
                    <div class="flex items-center p-3 border rounded-lg {{ $staff->can_manage_orders ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                        <svg class="w-5 h-5 {{ $staff->can_manage_orders ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="ml-2 text-sm {{ $staff->can_manage_orders ? 'text-emerald-700 font-medium' : 'text-gray-500' }}">Manage Orders</span>
                    </div>
                    <div class="flex items-center p-3 border rounded-lg {{ $staff->can_manage_settings ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                        <svg class="w-5 h-5 {{ $staff->can_manage_settings ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="ml-2 text-sm {{ $staff->can_manage_settings ? 'text-emerald-700 font-medium' : 'text-gray-500' }}">Manage Settings</span>
                    </div>
                    <div class="flex items-center p-3 border rounded-lg {{ $staff->can_view_reports ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                        <svg class="w-5 h-5 {{ $staff->can_view_reports ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="ml-2 text-sm {{ $staff->can_view_reports ? 'text-emerald-700 font-medium' : 'text-gray-500' }}">View Reports</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('seller.staff.toggle-status', $staff) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium rounded-lg {{ $staff->is_active ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                            {{ $staff->is_active ? 'Deactivate Staff' : 'Activate Staff' }}
                        </button>
                    </form>
                    <a href="{{ route('seller.staff.edit', $staff) }}" class="block w-full text-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                        Edit Staff
                    </a>
                    <form method="POST" action="{{ route('seller.staff.destroy', $staff) }}" x-data="{ confirm: false }">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="confirm = true" class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Remove Staff
                        </button>
                        <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
                            <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-xl">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Remove Staff Member?</h4>
                                <p class="text-sm text-gray-600 mb-4">This action cannot be undone. {{ $staff->name }} will lose access to your store.</p>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="confirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Remove</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-seller.layout>
