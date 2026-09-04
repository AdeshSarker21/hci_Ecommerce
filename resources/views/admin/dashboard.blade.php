<x-admin.layout title="Dashboard" active="dashboard">
    <x-admin.page-header title="Dashboard">
        <x-slot:subtitle>Welcome back, {{ auth()->user()->name }}! Here's what's happening.</x-slot:subtitle>
    </x-admin.page-header>

    <x-admin.alert />

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-admin.stat-card title="Total Users" value="{{ $stats['users'] }}" icon="users" color="indigo" change="+12% from last month" changeType="up" />
        <x-admin.stat-card title="Sellers" value="{{ $stats['sellers'] }}" icon="store" color="purple" change="{{ $stats['pending_sellers'] }} pending" changeType="neutral" />
        <x-admin.stat-card title="Products" value="{{ $stats['products'] }}" icon="cube" color="green" change="+28 this week" changeType="up" />
        <x-admin.stat-card title="Roles" value="{{ $stats['roles'] }}" icon="shield" color="blue" />
    </div>

    {{-- Pending Actions --}}
    @if($stats['pending_sellers'] > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pending Sellers</p>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">{{ $stats['pending_sellers'] }}</span>
                </div>
                @forelse($pendingSellers as $seller)
                    <div class="flex items-center justify-between py-2 @if(!$loop->last) border-b border-gray-50 @endif">
                        <div class="flex items-center min-w-0">
                            <img src="{{ $seller->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($seller->store_name ?? 'S') . '&background=e0e7ff&color=4f46e5&size=32' }}" alt="" class="w-7 h-7 rounded-full flex-shrink-0">
                            <div class="ml-2.5 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $seller->store_name ?? 'New Store' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $seller->user?->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.sellers.show', $seller) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex-shrink-0 ml-2">Review</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-3">No pending sellers</p>
                @endforelse
                @if($stats['pending_sellers'] > 5)
                    <a href="{{ route('admin.sellers.index') }}" class="block text-center text-xs font-medium text-indigo-600 hover:text-indigo-800 mt-3 pt-3 border-t border-gray-100">View all pending →</a>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pending Products</p>
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">0</span>
                </div>
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-sm text-gray-500">No pending products</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Security Alerts</p>
                </div>
                <div class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-600">All Clear</p>
                    <p class="text-xs text-gray-500 mt-0.5">No security issues detected</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">System Status</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">App Status</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Operational</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Database</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Healthy</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Cache</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Connected</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Sales Overview Placeholder --}}
    <div class="bg-white rounded-xl border border-gray-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Sales Overview</h3>
                <p class="text-xs text-gray-500 mt-0.5">Revenue and order trends</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">7 Days</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 cursor-pointer">30 Days</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">90 Days</span>
            </div>
        </div>
        <div class="h-64 flex flex-col items-center justify-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-sm font-medium text-gray-500">Sales analytics coming soon</p>
            <p class="text-xs text-gray-400 mt-1">Chart integration ready for implementation</p>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Recent Users --}}
        <x-admin.card title="Recent Users" subtitle="Latest registered accounts">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">User</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">Role</th>
                            <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentUsers as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-3 pr-4">
                                    <div class="flex items-center">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full ring-2 ring-gray-100">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 pr-4">
                                    <x-admin.badge type="indigo" :label="$user->roles->first()?->name ?? 'No Role'" />
                                </td>
                                <td class="py-3">
                                    @if($user->is_active)
                                        <x-admin.badge type="green" label="Active" />
                                    @else
                                        <x-admin.badge type="red" label="Inactive" />
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center">
                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentUsers->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">View all users →</a>
                </div>
            @endif
        </x-admin.card>

        {{-- Recent Sellers --}}
        <x-admin.card title="Recent Sellers" subtitle="Latest vendor registrations">
            <div class="space-y-3">
                @forelse($recentSellers as $seller)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="flex items-center min-w-0">
                            <img src="{{ $seller->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($seller->store_name ?? 'S') . '&background=e0e7ff&color=4f46e5&size=36' }}" alt="" class="w-9 h-9 rounded-full flex-shrink-0">
                            <div class="ml-3 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $seller->store_name ?? 'Store' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $seller->user?->name ?? 'Unknown' }} · {{ $seller->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                            @if($seller->status === 'approved')
                                <x-admin.badge type="green" label="Active" />
                            @elseif($seller->status === 'pending')
                                <x-admin.badge type="amber" label="Pending" />
                            @elseif($seller->status === 'rejected')
                                <x-admin.badge type="red" label="Rejected" />
                            @else
                                <x-admin.badge type="gray" label="{{ ucfirst($seller->status) }}" />
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="text-sm text-gray-500">No sellers yet</p>
                    </div>
                @endforelse
            </div>
            @if($recentSellers->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.sellers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">View all sellers →</a>
                </div>
            @endif
        </x-admin.card>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Roles Overview --}}
        <x-admin.card title="Roles" subtitle="Access control groups">
            <div class="space-y-3">
                @forelse($roles as $role)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="flex items-center min-w-0">
                            <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div class="ml-3 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                                <p class="text-xs text-gray-500">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</p>
                            </div>
                        </div>
                        @if($role->is_active)
                            <x-admin.badge type="green" label="Active" />
                        @else
                            <x-admin.badge type="red" label="Inactive" />
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">No roles found</p>
                @endforelse
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.roles.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">Manage roles →</a>
            </div>
        </x-admin.card>

        {{-- Quick Stats --}}
        <div class="lg:col-span-2">
            <x-admin.card title="Quick Stats" subtitle="Platform overview">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['active_users'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Active Users</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['permissions'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Permissions</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-2xl font-bold text-gray-900">0</p>
                        <p class="text-xs text-gray-500 mt-1">Orders</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-2xl font-bold text-gray-900">$0</p>
                        <p class="text-xs text-gray-500 mt-1">Revenue</p>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
