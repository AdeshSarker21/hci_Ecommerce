<x-admin.layout title="Brands" active="brands">
    <x-admin.page-header title="Brands">
        <x-slot:subtitle>Manage product brands</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.brands.create') }}" type="primary">Add Brand</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <x-admin.card>
        <form method="GET" action="{{ route('admin.brands.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brands..."
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($brands as $brand)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="w-10 h-10 rounded-lg object-cover">
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $brand->name }}</p>
                                        @if($brand->name_bn)
                                            <p class="text-xs text-gray-500">{{ $brand->name_bn }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400">{{ $brand->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $brand->status_badge }}">
                                    {{ ucfirst($brand->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $brand->sort_order }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $brand->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.brands.show', $brand) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="POST" action="{{ route('admin.brands.toggle-status', $brand) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $brand->isActive() ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                        {{ $brand->isActive() ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No brands found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $brands->links() }}
        </div>
    </x-admin.card>
</x-admin.layout>
