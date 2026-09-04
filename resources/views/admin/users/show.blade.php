<x-admin.layout title="User Details" active="users">
    <x-admin.page-header title="{{ $user->name }}">
        <x-slot:subtitle>User details and role assignments</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.users.index') }}" type="secondary">Back to Users</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- User Info --}}
        <div class="lg:col-span-2">
            <x-admin.card title="Profile Information">
                <div class="flex items-center space-x-6 mb-6">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-gray-500">{{ $user->email }}</p>
                        @if($user->phone)
                            <p class="text-sm text-gray-400 mt-1">{{ $user->phone }}</p>
                        @endif
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if($user->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->email_verified_at ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    @if($user->timezone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->timezone }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Locale</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ strtoupper($user->locale) }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </div>

        {{-- Roles --}}
        <div>
            <x-admin.card title="Roles">
                <div class="space-y-2">
                    @forelse($user->roles as $role)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $role->description ?? 'No description' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No roles assigned.</p>
                    @endforelse
                </div>
            </x-admin.card>

            @if($user->roles->flatMap->permissions->isNotEmpty())
                <x-admin.card title="Permissions" class="mt-6">
                    <div class="flex flex-wrap gap-1">
                        @foreach($user->roles->flatMap->permissions->unique('slug') as $permission)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">{{ $permission->name }}</span>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>
    </div>
</x-admin.layout>
