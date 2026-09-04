<x-admin.layout title="Create Attribute" active="attributes">
    <x-admin.page-header title="Create Attribute">
        <x-slot:subtitle>Add a new product attribute</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.attributes.index') }}" type="secondary">Back to Attributes</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <form method="POST" action="{{ route('admin.attributes.store') }}">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-admin.card title="Attribute Information">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">Name (Bangla)</label>
                                <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn') }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                            <select name="type" id="type" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Text</option>
                                <option value="textarea" {{ old('type') === 'textarea' ? 'selected' : '' }}>Textarea</option>
                                <option value="number" {{ old('type') === 'number' ? 'selected' : '' }}>Number</option>
                                <option value="select" {{ old('type') === 'select' ? 'selected' : '' }}>Select</option>
                                <option value="radio" {{ old('type') === 'radio' ? 'selected' : '' }}>Radio</option>
                                <option value="checkbox" {{ old('type') === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                <option value="color" {{ old('type') === 'color' ? 'selected' : '' }}>Color</option>
                                <option value="date" {{ old('type') === 'date' ? 'selected' : '' }}>Date</option>
                            </select>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Attribute Values">
                    <p class="text-sm text-gray-500 mb-4">Add possible values for this attribute (e.g., for "Color" add Red, Blue, etc.)</p>
                    <div id="values-container" class="space-y-3">
                        <div class="flex items-center gap-3 value-row">
                            <input type="text" name="values[0][value]" placeholder="Value (English)" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <input type="text" name="values[0][value_bn]" placeholder="Value (Bangla)" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <input type="text" name="values[0][color_code]" placeholder="#hex" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <button type="button" onclick="this.closest('.value-row').remove()" class="text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="button" onclick="addValue()" class="mt-3 inline-flex items-center px-3 py-1.5 text-sm text-indigo-600 hover:text-indigo-800">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Value
                    </button>
                </x-admin.card>
            </div>

            <div class="space-y-6">
                <x-admin.card title="Settings">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_filterable" value="1" {{ old('is_filterable') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Filterable</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_variant" value="1" {{ old('is_variant') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Used for Variants</span>
                            </label>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </x-admin.card>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Attribute
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        let valueIndex = 1;
        function addValue() {
            const container = document.getElementById('values-container');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 value-row';
            row.innerHTML = `
                <input type="text" name="values[${valueIndex}][value]" placeholder="Value (English)" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <input type="text" name="values[${valueIndex}][value_bn]" placeholder="Value (Bangla)" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <input type="text" name="values[${valueIndex}][color_code]" placeholder="#hex" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <button type="button" onclick="this.closest('.value-row').remove()" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(row);
            valueIndex++;
        }
    </script>
</x-admin.layout>
