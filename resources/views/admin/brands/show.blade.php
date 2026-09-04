<x-admin.layout title="Brand Details" active="brands">
    <x-admin.page-header title="{{ $brand->name }}">
        <x-slot:subtitle>Brand details and management</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.brands.edit', $brand) }}" type="primary">Edit</x-admin.button>
            <x-admin.button href="{{ route('admin.brands.index') }}" type="secondary">Back</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Brand Information">
                <div class="flex items-center space-x-6 mb-6">
                    <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="w-20 h-20 rounded-xl object-cover">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $brand->name }}</h3>
                        @if($brand->name_bn)
                            <p class="text-gray-500">{{ $brand->name_bn }}</p>
                        @endif
                        <p class="text-sm text-gray-400">{{ $brand->slug }}</p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $brand->status_badge }}">
                                {{ ucfirst($brand->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $brand->sort_order }}</dd>
                    </div>
                    @if($brand->website)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="mt-1 text-sm"><a href="{{ $brand->website }}" target="_blank" class="text-indigo-600 hover:underline">{{ $brand->website }}</a></dd>
                        </div>
                    @endif
                    @if($brand->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $brand->description }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $brand->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $brand->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card title="Actions">
                <div class="space-y-3">
                    <a href="{{ route('admin.brands.edit', $brand) }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        Edit Brand
                    </a>
                    <form method="POST" action="{{ route('admin.brands.toggle-status', $brand) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium rounded-lg {{ $brand->isActive() ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                            {{ $brand->isActive() ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" x-data="{ confirm: false }">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="confirm = true" class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Delete Brand
                        </button>
                        <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
                            <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-xl">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Delete Brand?</h4>
                                <p class="text-sm text-gray-600 mb-4">This action cannot be undone.</p>
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
