<x-admin.layout title="Edit {{ $courier->name }}" active="couriers">
    <div class="mb-6">
        <a href="{{ route('admin.couriers.show', $courier) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to {{ $courier->name }}
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Edit {{ $courier->name }}</h2>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.couriers.update', $courier) }}">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $courier->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                            <input type="url" name="website" value="{{ old('website', $courier->website) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                            <input type="url" name="logo" value="{{ old('logo', $courier->logo) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">{{ old('description', $courier->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">API Configuration</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Base URL</label>
                            <input type="url" name="api_base_url" value="{{ old('api_base_url', $courier->api_base_url) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                            <input type="password" name="api_key" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Leave blank to keep current">
                            <p class="text-xs text-gray-400 mt-1">{{ $courier->api_key ? 'Key is set. Enter new value to change.' : 'No key configured.' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                            <input type="password" name="api_secret" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Leave blank to keep current">
                            <p class="text-xs text-gray-400 mt-1">{{ $courier->api_secret ? 'Secret is set. Enter new value to change.' : 'No secret configured.' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                            <input type="password" name="webhook_secret" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Leave blank to keep current">
                            <p class="text-xs text-gray-400 mt-1">{{ $courier->webhook_secret ? 'Secret is set.' : 'No webhook secret configured.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $courier->is_active) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="text-sm text-gray-700">Active</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1" {{ old('is_default', $courier->is_default) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="text-sm text-gray-700">Set as Default</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $courier->sort_order) }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Supported Services</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="cod_support" value="0">
                            <input type="checkbox" name="cod_support" value="1" {{ old('cod_support', $courier->cod_support) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="text-sm text-gray-700">COD Support</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="tracking_support" value="0">
                            <input type="checkbox" name="tracking_support" value="1" {{ old('tracking_support', $courier->tracking_support) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="text-sm text-gray-700">Tracking Support</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="shipment_creation_support" value="0">
                            <input type="checkbox" name="shipment_creation_support" value="1" {{ old('shipment_creation_support', $courier->shipment_creation_support) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="text-sm text-gray-700">Shipment Creation</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="return_support" value="0">
                            <input type="checkbox" name="return_support" value="1" {{ old('return_support', $courier->return_support) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label class="text-sm text-gray-700">Return Support</label>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">COD Fees</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fixed COD Fee ($)</label>
                            <input type="number" name="cod_fee" value="{{ old('cod_fee', $courier->cod_fee) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">COD Percentage (%)</label>
                            <input type="number" name="cod_percentage" value="{{ old('cod_percentage', $courier->cod_percentage) }}" step="0.01" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Weight Limit (kg)</label>
                            <input type="number" name="weight_limit_kg" value="{{ old('weight_limit_kg', $courier->weight_limit_kg) }}" step="0.1" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Optional">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Shipment Value ($)</label>
                            <input type="number" name="max_value" value="{{ old('max_value', $courier->max_value) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Optional">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Update Courier</button>
                    <a href="{{ route('admin.couriers.show', $courier) }}" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-admin.layout>
