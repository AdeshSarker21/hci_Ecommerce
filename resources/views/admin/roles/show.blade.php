<x-admin.layout title="Role Details" active="roles">
    <x-admin.page-header title="{{ $role->name }}">
        <x-slot:subtitle>Role details and assigned permissions</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.roles.index') }}" type="secondary">Back to Roles</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-admin.card title="Role Information">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $role->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Slug</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $role->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $role->is_system ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $role->is_system ? 'System' : 'Custom' }}
                            </span>
                        </dd>
                    </div>
                    @if($role->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $role->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-admin.card>

            <x-admin.card title="Permissions" class="mt-6">
                @if($role->permissions->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($role->permissions as $permission)
                            <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                <span class="ml-auto text-xs text-gray-400">{{ $permission->group }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No permissions assigned to this role.</p>
                @endif
            </x-admin.card>
        </div>

        <div>
            <x-admin.card title="Users with this Role">
                <div class="space-y-2">
                    @forelse($role->users->take(10) as $user)
                        <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No users with this role.</p>
                    @endforelse
                    @if($role->users->count() > 10)
                        <p class="text-xs text-gray-400 text-center">+ {{ $role->users->count() - 10 }} more users</p>
                    @endif
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
