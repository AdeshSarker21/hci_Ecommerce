<x-admin.layout title="Dashboard" active="dashboard">
    <x-admin.page-header title="Dashboard">
        <x-slot:subtitle>Welcome back, {{ auth()->user()->name }}!</x-slot:subtitle>
    </x-admin.page-header>

    <x-admin.alert />

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-admin.stat-card title="Total Users" value="{{ $stats['users'] }}" icon="users" />
        <x-admin.stat-card title="Active Users" value="{{ $stats['active_users'] }}" icon="users" />
        <x-admin.stat-card title="Roles" value="{{ $stats['roles'] }}" icon="shield" />
        <x-admin.stat-card title="Permissions" value="{{ $stats['permissions'] }}" icon="key" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Users --}}
        <div class="lg:col-span-2">
            <x-admin.card title="Recent Users">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentUsers as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            {{ $user->roles->first()?->name ?? 'No Role' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>

        {{-- Roles Overview --}}
        <div>
            <x-admin.card title="Roles">
                <div class="space-y-3">
                    @forelse($roles as $role)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                                <p class="text-xs text-gray-500">{{ $role->users_count }} users</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No roles found.</p>
                    @endforelse
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
