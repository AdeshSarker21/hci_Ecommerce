<x-admin.layout title="Manage Spec Fields - {{ $category->name }}" active="category-attributes">
    <x-admin.page-header title="Manage Spec Fields">
        <x-slot:subtitle>Assign specification fields to "{{ $category->name }}"</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.category-attributes.index') }}" type="secondary">Back to List</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <form method="POST" action="{{ route('admin.category-attributes.update', $category) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Attribute Selection --}}
            <div class="lg:col-span-2">
                <x-admin.card>
                    <x-slot:title>Select Specification Fields</x-slot:title>

                    <p class="text-sm text-gray-500 mb-4">Choose which specification fields apply to products in this category.</p>

                    <div class="space-y-3">
                        @forelse($allAttributes as $attribute)
                            @php
                                $assigned = $category->attributes->contains('id', $attribute->id);
                                $pivot = $category->attributes->firstWhere('id', $attribute->id)?->pivot;
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-lg border {{ $assigned ? 'border-indigo-200 bg-indigo-50' : 'border-gray-200 bg-white' }} hover:border-indigo-300 transition-colors"
                                 x-data="{ enabled: {{ $assigned ? 'true' : 'false' }} }">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" name="attributes[]" value="{{ $attribute->id }}"
                                           id="attr-{{ $attribute->id }}"
                                           x-model="enabled"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="attr-{{ $attribute->id }}" class="cursor-pointer">
                                        <p class="text-sm font-medium text-gray-900">{{ $attribute->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $attribute->type }} @if($attribute->name_bn) | {{ $attribute->name_bn }} @endif</p>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-4" x-show="enabled" x-transition>
                                    <div class="flex items-center space-x-2">
                                        <label class="text-xs text-gray-500">Order:</label>
                                        <input type="number" name="sort_order[{{ $attribute->id }}]"
                                               value="{{ $pivot?->sort_order ?? 0 }}" min="0"
                                               class="w-16 rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <label class="flex items-center space-x-1.5">
                                        <input type="checkbox" name="is_required[{{ $attribute->id }}]" value="1"
                                               {{ $pivot?->is_required ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-xs text-gray-500">Required</span>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">No attributes available. Create attributes first.</p>
                        @endforelse
                    </div>
                </x-admin.card>
            </div>

            {{-- Summary --}}
            <div class="space-y-6">
                <x-admin.card>
                    <x-slot:title>Category Info</x-slot:title>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Name</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $category->name }}</dd>
                        </div>
                        @if($category->name_bn)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase">Name (Bangla)</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $category->name_bn }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Currently Assigned</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $category->attributes->count() }} fields</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Required Fields</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $category->attributes->where('pivot.is_required', true)->count() }}</dd>
                        </div>
                    </dl>
                </x-admin.card>

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-admin.layout>
