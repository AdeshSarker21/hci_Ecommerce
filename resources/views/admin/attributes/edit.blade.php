<x-admin.layout title="Edit Attribute" active="attributes">
    <x-admin.page-header title="Edit Attribute">
        <x-slot:subtitle>Update "{{ $attribute->name }}"</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.attributes.index') }}" type="secondary">Back to Attributes</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <form method="POST" action="{{ route('admin.attributes.update', $attribute) }}">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-admin.card title="Attribute Information">
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name (English) *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $attribute->name) }}" required placeholder="e.g. Color, Size, Material"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                            </div>
                            <div>
                                <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1.5">Name (Bangla)</label>
                                <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $attribute->name_bn) }}" placeholder="বাংলায় নাম"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                            </div>
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">Type *</label>
                            <select name="type" id="type" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="text" {{ old('type', $attribute->type) === 'text' ? 'selected' : '' }}>Text</option>
                                <option value="textarea" {{ old('type', $attribute->type) === 'textarea' ? 'selected' : '' }}>Textarea</option>
                                <option value="number" {{ old('type', $attribute->type) === 'number' ? 'selected' : '' }}>Number</option>
                                <option value="select" {{ old('type', $attribute->type) === 'select' ? 'selected' : '' }}>Select</option>
                                <option value="radio" {{ old('type', $attribute->type) === 'radio' ? 'selected' : '' }}>Radio</option>
                                <option value="checkbox" {{ old('type', $attribute->type) === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                <option value="color" {{ old('type', $attribute->type) === 'color' ? 'selected' : '' }}>Color</option>
                                <option value="date" {{ old('type', $attribute->type) === 'date' ? 'selected' : '' }}>Date</option>
                            </select>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Attribute Values">
                    <p class="text-sm text-gray-500 mb-4">Manage the values for this attribute.</p>
                    <div id="values-container" class="space-y-3">
                        @foreach($attribute->values as $index => $value)
                            <div class="flex items-center gap-3 value-row">
                                <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">
                                <input type="text" name="values[{{ $index }}][value]" value="{{ $value->value }}" placeholder="Value (English)" class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <input type="text" name="values[{{ $index }}][value_bn]" value="{{ $value->value_bn }}" placeholder="Value (Bangla)" class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <input type="text" name="values[{{ $index }}][color_code]" value="{{ $value->color_code }}" placeholder="#hex" class="w-24 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <button type="button" onclick="this.closest('.value-row').remove()" class="flex-shrink-0 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addValue()" class="mt-4 inline-flex items-center px-3 py-2 text-sm text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Value
                    </button>
                </x-admin.card>
            </div>

            <div class="space-y-6">
                <x-admin.card title="Settings">
                    <div class="space-y-5">
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_required" value="1" {{ old('is_required', $attribute->is_required) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2.5 text-sm text-gray-700">Required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_filterable" value="1" {{ old('is_filterable', $attribute->is_filterable) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2.5 text-sm text-gray-700">Filterable</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_variant" value="1" {{ old('is_variant', $attribute->is_variant) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2.5 text-sm text-gray-700">Used for Variants</span>
                            </label>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status *</label>
                            <select name="status" id="status" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="active" {{ old('status', $attribute->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $attribute->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $attribute->sort_order) }}" min="0" placeholder="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                    </div>
                </x-admin.card>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Update Attribute
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        let valueIndex = {{ $attribute->values->count() }};
        function addValue() {
            const container = document.getElementById('values-container');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 value-row';
            row.innerHTML = `
                <input type="text" name="values[${valueIndex}][value]" placeholder="Value (English)" class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                <input type="text" name="values[${valueIndex}][value_bn]" placeholder="Value (Bangla)" class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                <input type="text" name="values[${valueIndex}][color_code]" placeholder="#hex" class="w-24 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                <button type="button" onclick="this.closest('.value-row').remove()" class="flex-shrink-0 p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(row);
            valueIndex++;
        }
    </script>
</x-admin.layout>
