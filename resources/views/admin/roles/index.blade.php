<x-admin.layout title="Roles" active="roles">
    <x-admin.page-header title="Roles">
        <x-slot:subtitle>Manage system roles and their permissions</x-slot:subtitle>
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $role->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $role->slug }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $role->users_count }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($role->permissions->take(5) as $perm)
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">{{ $perm->name }}</span>
                                    @empty
                                        <span class="text-xs text-gray-400">None</span>
                                    @endforelse
                                    @if($role->permissions->count() > 5)
                                        <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-600">+{{ $role->permissions->count() - 5 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $role->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($role->is_system)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">System</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Custom</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.roles.show', $role) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin.layout>
