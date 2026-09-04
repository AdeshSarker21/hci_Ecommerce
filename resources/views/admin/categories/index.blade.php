<x-admin.layout title="Categories" active="categories">
    <x-admin.page-header title="Categories">
        <x-slot:subtitle>Manage product categories and hierarchy</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.categories.create') }}" type="primary">Add Category</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <x-admin.card>
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..."
                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="parent_id" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Categories</option>
                <option value="">Root Categories</option>
                @foreach($rootCategories as $cat)
                    <option value="{{ $cat->id }}" {{ request('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <x-admin.button type="submit" size="md">Filter</x-admin.button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sort</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($category->image)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                                        @if($category->name_bn)
                                            <p class="text-xs text-gray-500">{{ $category->name_bn }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $category->parent ? $category->parent->name : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->status_badge }}">
                                    {{ ucfirst($category->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $category->children_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.categories.show', $category) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $category->isActive() ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                        {{ $category->isActive() ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </x-admin.card>
</x-admin.layout>
