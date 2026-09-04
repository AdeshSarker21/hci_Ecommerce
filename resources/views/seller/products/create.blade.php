<x-seller.layout title="Create Product" active="products">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Product</h2>
            <p class="text-sm text-gray-500 mt-1">Add a new product to your store</p>
        </div>
        <a href="{{ route('seller.products.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
            Back to Products
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium text-red-700">Please fix the following errors:</p>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('seller.products.store') }}" x-data="productSpecs()" x-init="init()">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name (English) *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">Product Name (Bangla)</label>
                            <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                            <textarea name="description" id="description" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label for="description_bn" class="block text-sm font-medium text-gray-700 mb-1">Description (Bangla)</label>
                            <textarea name="description_bn" id="description_bn" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">{{ old('description_bn') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing & Stock</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                            <input type="number" name="price" id="price" value="{{ old('price') }}" required step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="compare_at_price" class="block text-sm font-medium text-gray-700 mb-1">Compare at Price</label>
                            <input type="number" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                            <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center mt-6">
                                <input type="checkbox" name="manage_stock" value="1" {{ old('manage_stock', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-2 text-sm text-gray-700">Manage Stock</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
                            <select name="type" id="type" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="physical" {{ old('type') === 'physical' ? 'selected' : '' }}>Physical</option>
                                <option value="digital" {{ old('type') === 'digital' ? 'selected' : '' }}>Digital</option>
                                <option value="service" {{ old('type') === 'service' ? 'selected' : '' }}>Service</option>
                            </select>
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category_id" id="category_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <select name="brand_id" id="brand_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                   placeholder="Auto-generated if empty">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" rows="3"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">{{ old('meta_description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Specification Fields --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="specFields.length > 0" x-transition>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Specifications</h3>
                    <div class="space-y-4">
                        <template x-for="field in specFields" :key="field.id">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    <span x-text="field.name"></span>
                                    <span x-show="field.is_required" class="text-red-500">*</span>
                                    <span x-show="field.name_bn" class="text-gray-400 text-xs" x-text="'(' + field.name_bn + ')'"></span>
                                </label>

                                {{-- Text / Number --}}
                                <template x-if="field.type === 'text' || field.type === 'number' || field.type === 'textarea'">
                                    <input :type="field.type === 'number' ? 'number' : 'text'"
                                           :name="'spec[' + field.id + ']'"
                                           x-model="values[field.id]"
                                           :required="field.is_required"
                                           :step="field.type === 'number' ? 'any' : undefined"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                           :placeholder="'Enter ' + field.name.toLowerCase()">
                                </template>

                                {{-- Select --}}
                                <template x-if="field.type === 'select'">
                                    <select :name="'spec[' + field.id + ']'"
                                            x-model="values[field.id]"
                                            :required="field.is_required"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                        <option value="">Select {{ field.name }}</option>
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <option :value="opt.value" x-text="opt.value_bn ? opt.value + ' (' + opt.value_bn + ')' : opt.value"></option>
                                        </template>
                                    </select>
                                </template>

                                {{-- Multi-select --}}
                                <template x-if="field.type === 'multiselect' || field.type === 'checkbox'">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <label class="flex items-center space-x-2 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-emerald-300 cursor-pointer transition-colors"
                                                   :class="multiValues[field.id]?.includes(opt.value) ? 'border-emerald-500 bg-emerald-50' : ''">
                                                <input type="checkbox"
                                                       :name="'spec[' + field.id + '][]'"
                                                       :value="opt.value"
                                                       x-model="multiValues[field.id]"
                                                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-sm text-gray-700" x-text="opt.value_bn ? opt.value + ' (' + opt.value_bn + ')' : opt.value"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>

                                {{-- Boolean --}}
                                <template x-if="field.type === 'boolean' || field.type === 'radio'">
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" :name="'spec[' + field.id + ']'" value="1"
                                                   x-model="values[field.id]" class="border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-gray-700">Yes</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" :name="'spec[' + field.id + ']'" value="0"
                                                   x-model="values[field.id]" class="border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-gray-700">No</span>
                                        </label>
                                    </div>
                                </template>

                                {{-- Color --}}
                                <template x-if="field.type === 'color'">
                                    <div class="flex items-center space-x-3">
                                        <input type="color" :name="'spec[' + field.id + ']'"
                                               x-model="values[field.id]"
                                               class="h-10 w-10 rounded-lg border border-gray-300 cursor-pointer">
                                        <input type="text" :name="'spec[' + field.id + ']'"
                                               x-model="values[field.id]"
                                               class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                               placeholder="#000000">
                                    </div>
                                </template>

                                {{-- Date --}}
                                <template x-if="field.type === 'date'">
                                    <input type="date" :name="'spec[' + field.id + ']'"
                                           x-model="values[field.id]"
                                           :required="field.is_required"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Create Product
                    </button>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function productSpecs() {
            return {
                specFields: [],
                values: {},
                multiValues: {},
                init() {
                    const categoryId = document.getElementById('category_id');
                    if (categoryId.value) {
                        this.loadSpecs(categoryId.value);
                    }
                    categoryId.addEventListener('change', (e) => {
                        if (e.target.value) {
                            this.loadSpecs(e.target.value);
                        } else {
                            this.specFields = [];
                            this.values = {};
                            this.multiValues = {};
                        }
                    });
                },
                async loadSpecs(categoryId) {
                    try {
                        const response = await fetch(`/seller/category-attributes/${categoryId}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (response.ok) {
                            this.specFields = await response.json();
                            this.specFields.forEach(field => {
                                if (['multiselect', 'checkbox'].includes(field.type)) {
                                    if (!this.multiValues[field.id]) this.multiValues[field.id] = [];
                                } else {
                                    if (!this.values[field.id]) this.values[field.id] = '';
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Failed to load specs:', e);
                    }
                }
            };
        }
    </script>
    @endpush
</x-seller.layout>
