<x-admin.layout title="Edit Brand" active="brands">
    <x-admin.page-header title="Edit Brand">
        <x-slot:subtitle>Update "{{ $brand->name }}"</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.brands.index') }}" type="secondary">Back to Brands</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-admin.card title="Brand Information">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">Name (Bangla)</label>
                            <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $brand->name_bn) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $brand->description) }}</textarea>
                        </div>
                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                            <input type="url" name="website" id="website" value="{{ old('website', $brand->website) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <div class="space-y-6">
                <x-admin.card title="Logo & Settings">
                    <div class="space-y-4">
                        <div>
                            <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                            <input type="text" name="logo" id="logo" value="{{ old('logo', $brand->logo) }}"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="active" {{ old('status', $brand->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $brand->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $brand->sort_order) }}" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </x-admin.card>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Update Brand
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-admin.layout>
