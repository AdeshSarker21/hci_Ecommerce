<x-admin.layout title="Attribute Details" active="attributes">
    <x-admin.page-header title="{{ $attribute->name }}">
        <x-slot:subtitle>Attribute details and values</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.attributes.edit', $attribute) }}" type="primary">Edit</x-admin.button>
            <x-admin.button href="{{ route('admin.attributes.index') }}" type="secondary">Back</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Attribute Information">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name (English)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name (Bangla)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->name_bn ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Slug</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($attribute->type) }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attribute->status_badge }}">{{ ucfirst($attribute->status) }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->sort_order }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Required</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->is_required ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Filterable</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->is_filterable ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Variant</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $attribute->is_variant ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            <x-admin.card title="Attribute Values">
                @if($attribute->values->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bangla</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sort</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($attribute->values as $value)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $value->value }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $value->value_bn ?: '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $value->slug }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            @if($value->color_code)
                                                <div class="flex items-center">
                                                    <span class="w-4 h-4 rounded-full border mr-2" style="background-color: {{ $value->color_code }}"></span>
                                                    {{ $value->color_code }}
                                                </div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $value->sort_order }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-4">No values defined for this attribute.</p>
                @endif
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card title="Actions">
                <div class="space-y-3">
                    <a href="{{ route('admin.attributes.edit', $attribute) }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                        Edit Attribute
                    </a>
                    <form method="POST" action="{{ route('admin.attributes.toggle-status', $attribute) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium rounded-lg {{ $attribute->isActive() ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                            {{ $attribute->isActive() ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}" x-data="{ confirm: false }">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="confirm = true" class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                            Delete Attribute
                        </button>
                        <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
                            <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-xl">
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">Delete Attribute?</h4>
                                <p class="text-sm text-gray-600 mb-4">All associated values will also be deleted. This action cannot be undone.</p>
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
