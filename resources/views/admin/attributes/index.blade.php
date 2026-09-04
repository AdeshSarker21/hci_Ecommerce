<x-admin.layout title="Attributes" active="attributes">
    <x-admin.page-header title="Attributes">
        <x-slot:subtitle>Manage product attributes and values</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.attributes.create') }}" type="primary">Add Attribute</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <x-admin.card>
        <form method="GET" action="{{ route('admin.attributes.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search attributes..."
                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <x-admin.button type="submit" size="md">Filter</x-admin.button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attribute</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Values</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Options</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attributes as $attribute)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $attribute->name }}</p>
                                    @if($attribute->name_bn)
                                        <p class="text-xs text-gray-500">{{ $attribute->name_bn }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400">{{ $attribute->slug }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($attribute->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attribute->values->count() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @if($attribute->is_required)
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-red-100 text-red-700">Required</span>
                                    @endif
                                    @if($attribute->is_filterable)
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-blue-100 text-blue-700">Filter</span>
                                    @endif
                                    @if($attribute->is_variant)
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-purple-100 text-purple-700">Variant</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attribute->status_badge }}">
                                    {{ ucfirst($attribute->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.attributes.show', $attribute) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('admin.attributes.edit', $attribute) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="POST" action="{{ route('admin.attributes.toggle-status', $attribute) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $attribute->isActive() ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                        {{ $attribute->isActive() ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No attributes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $attributes->links() }}
        </div>
    </x-admin.card>
</x-admin.layout>
