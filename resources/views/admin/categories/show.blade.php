<x-admin.layout title="Category Details" active="categories">
    <x-admin.page-header title="{{ $category->name }}">
        <x-slot:subtitle>Category details and management</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.categories.edit', $category) }}" type="primary">Edit</x-admin.button>
            <x-admin.button href="{{ route('admin.categories.index') }}" type="secondary">Back</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Category Information">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name (English)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name (Bangla)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->name_bn ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Slug</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Parent</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->parent ? $category->parent->name : 'Root Category' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->status_badge }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->sort_order }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Depth</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->depth }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Children</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $childrenCount }}</dd>
                    </div>
                    @if($category->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $category->description }}</dd>
                        </div>
                    @endif
                    @if($category->image)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Image</dt>
                            <dd class="mt-1"><img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-32 h-32 rounded-lg object-cover"></dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $category->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            @if($category->children->isNotEmpty())
                <x-admin.card title="Child Categories">
                    <div class="space-y-2">
                        @foreach($category->children as $child)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded bg-indigo-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $child->name }}</p>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $child->status_badge }}">{{ ucfirst($child->status) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('admin.categories.show', $child) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>

        <div class="space-y-6">
            <x-admin.card title="Actions">
                <div class="space-y-3">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        Edit Category
                    </a>
                    <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium rounded-lg {{ $category->isActive() ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                            {{ $category->isActive() ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" x-data="{ confirm: false }">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="confirm = true" class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Delete Category
                        </button>
                        <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
                            <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-xl">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Delete Category?</h4>
                                <p class="text-sm text-gray-600 mb-4">Child categories will be reassigned to the parent. This action cannot be undone.</p>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="confirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
