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
                <p class="text-sm font-medium text-red-700">Please fix {{ $errors->count() }} error(s) below:</p>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('seller.products.store') }}" enctype="multipart/form-data"
          x-data="productForm()" x-init="init()" id="productForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ========== LEFT COLUMN (2/3) ========== --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Section 1: Basic Information --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name (English) *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                   placeholder="e.g. Wireless Bluetooth Headphones">
                        </div>
                        <div>
                            <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">Product Name (Bangla)</label>
                            <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                   placeholder="বাংলায় পণ্যের নাম">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                            <textarea name="description" id="description" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                      placeholder="Detailed product description...">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label for="description_bn" class="block text-sm font-medium text-gray-700 mb-1">Description (Bangla)</label>
                            <textarea name="description_bn" id="description_bn" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                      placeholder="পণ্যের বিস্তারিত বিবরণ...">{{ old('description_bn') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Category, Brand & Type --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Category, Brand & Type</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category_id" id="category_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <select name="brand_id" id="brand_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Product Type *</label>
                            <select name="type" id="type" required x-model="productType"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="physical">Physical Product</option>
                                <option value="digital">Digital Product</option>
                                <option value="service">Service</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Price & Discount --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Price & Discount</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-gray-500">$</span>
                                <input type="number" name="price" id="price" value="{{ old('price') }}" required step="0.01" min="0"
                                       class="w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                       placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label for="compare_at_price" class="block text-sm font-medium text-gray-700 mb-1">Compare at Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-gray-500">$</span>
                                <input type="number" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price') }}" step="0.01" min="0"
                                       class="w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Original price for showing discount</p>
                        </div>
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-gray-500">$</span>
                                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0"
                                       class="w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Your cost (not shown to customers)</p>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg" x-show="discountPercent > 0" x-transition>
                        <p class="text-sm text-emerald-600 font-medium">
                            Discount: <span x-text="discountPercent + '%'"></span> off
                        </p>
                    </div>
                </div>

                {{-- Section 4: SKU & Identifiers --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SKU & Identifiers</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                   placeholder="Auto-generated if empty">
                            <p class="mt-1 text-xs text-gray-400">Leave blank to auto-generate</p>
                        </div>
                        <div class="flex items-end">
                            <div class="w-full p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Barcode & QR Code</p>
                                <p class="text-sm text-gray-700">Auto-generated from SKU after saving</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 5: Specifications (Category Attributes) --}}
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

                                {{-- Text / Number / Textarea --}}
                                <template x-if="field.type === 'text' || field.type === 'number' || field.type === 'textarea'">
                                    <div>
                                        <textarea x-if="field.type === 'textarea'"
                                                  :name="'spec[' + field.id + ']'"
                                                  x-model="values[field.id]"
                                                  :required="field.is_required"
                                                  rows="3"
                                                  class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                                  :placeholder="'Enter ' + field.name.toLowerCase()"></textarea>
                                        <input x-if="field.type !== 'textarea'"
                                               :type="field.type === 'number' ? 'number' : 'text'"
                                               :name="'spec[' + field.id + ']'"
                                               x-model="values[field.id]"
                                               :required="field.is_required"
                                               :step="field.type === 'number' ? 'any' : undefined"
                                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                               :placeholder="'Enter ' + field.name.toLowerCase()">
                                    </div>
                                </template>

                                {{-- Select --}}
                                <template x-if="field.type === 'select'">
                                    <select :name="'spec[' + field.id + ']'"
                                            x-model="values[field.id]"
                                            :required="field.is_required"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                        <option value="">Select @{{ field.name }}</option>
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <option :value="opt.value" x-text="opt.value_bn ? opt.value + ' (' + opt.value_bn + ')' : opt.value"></option>
                                        </template>
                                    </select>
                                </template>

                                {{-- Multiselect / Checkbox --}}
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

                                {{-- Boolean / Radio --}}
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

                {{-- Section 6: Product Images --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Images</h3>
                    <div class="space-y-4">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-400 transition-colors"
                             x-on:click="$refs.fileInput.click()"
                             x-on:dragover.prevent="dragover = true"
                             x-on:dragleave.prevent="dragover = false"
                             x-on:drop.prevent="handleDrop($event)"
                             :class="dragover ? 'border-emerald-400 bg-emerald-50' : ''">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">Drag and drop images here, or <span class="text-emerald-600 font-medium">browse</span></p>
                            <p class="mt-1 text-xs text-gray-400">JPEG, PNG, GIF, WebP up to 5MB each. Max 10 images.</p>
                        </div>
                        <input type="file" name="images[]" x-ref="fileInput" multiple accept="image/*"
                               class="hidden" x-on:change="handleFiles($event.target.files)">

                        {{-- Image Preview Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" x-show="previews.length > 0">
                            <template x-for="(preview, index) in previews" :key="index">
                                <div class="relative group rounded-lg overflow-hidden border-2"
                                     :class="featuredIndex === index ? 'border-emerald-500' : 'border-gray-200'"
                                     draggable="true"
                                     x-on:dragstart="dragStart($event, index)"
                                     x-on:dragover.prevent="dragOver($event, index)"
                                     x-on:drop.prevent="drop($event, index)">
                                    <img :src="preview.url" class="w-full aspect-square object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                        <button type="button" x-on:click="featuredIndex = index"
                                                class="p-1.5 rounded-full text-white transition-colors"
                                                :class="featuredIndex === index ? 'bg-emerald-500' : 'bg-white/80 hover:bg-white'"
                                                title="Set as featured">
                                            <svg class="w-4 h-4" :class="featuredIndex === index ? 'text-white' : 'text-gray-700'" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </button>
                                        <button type="button" x-on:click="removeNewImage(index)"
                                                class="p-1.5 rounded-full bg-white/80 hover:bg-white text-red-500 transition-colors" title="Remove">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="absolute top-1 left-1" x-show="featuredIndex === index">
                                        <span class="px-1.5 py-0.5 bg-emerald-500 text-white text-xs font-medium rounded">Featured</span>
                                    </div>
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                                        <input type="text" :name="'image_alt_texts[' + index + ']'"
                                               x-model="altTexts[index]"
                                               class="w-full text-xs bg-transparent text-white placeholder-white/70 border-0 p-0 focus:ring-0"
                                               placeholder="Alt text">
                                    </div>
                                </div>
                            </template>
                        </div>
                        <input type="hidden" name="featured_image_index" :value="featuredIndex">
                    </div>
                </div>

                {{-- Section 7: Inventory --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="productType === 'physical'" x-transition>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity in Stock</label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 0) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert At</label>
                            <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center pb-2.5">
                                <input type="checkbox" name="manage_stock" value="1" {{ old('manage_stock', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-2 text-sm text-gray-700">Track Inventory</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Section 8: Shipping --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="productType === 'physical'" x-transition>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" name="weight" id="weight" value="{{ old('weight') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label for="shipping_class" class="block text-sm font-medium text-gray-700 mb-1">Shipping Class</label>
                            <select name="shipping_class" id="shipping_class"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Standard</option>
                                <option value="heavy" {{ old('shipping_class') === 'heavy' ? 'selected' : '' }}>Heavy/Bulky</option>
                                <option value="fragile" {{ old('shipping_class') === 'fragile' ? 'selected' : '' }}>Fragile</option>
                                <option value="free" {{ old('shipping_class') === 'free' ? 'selected' : '' }}>Free Shipping</option>
                            </select>
                        </div>
                        <div>
                            <label for="length" class="block text-sm font-medium text-gray-700 mb-1">Length (cm)</label>
                            <input type="number" name="length" id="length" value="{{ old('length') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="width" class="block text-sm font-medium text-gray-700 mb-1">Width (cm)</label>
                            <input type="number" name="width" id="width" value="{{ old('width') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                            <input type="number" name="height" id="height" value="{{ old('height') }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                    </div>
                </div>

                {{-- Section 9: SEO --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                   placeholder="SEO page title">
                            <p class="mt-1 text-xs text-gray-400" x-text="(metaTitle || '').length + '/60 characters'"></p>
                        </div>
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" rows="3"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                      placeholder="SEO page description" x-model="metaDesc"></textarea>
                            <p class="mt-1 text-xs text-gray-400" x-text="metaDesc.length + '/160 characters'"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== RIGHT COLUMN (1/3) ========== --}}
            <div class="space-y-6">

                {{-- Section 10: Status & Visibility --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Visibility</h3>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-gray-700">Active (visible when published)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                        </label>
                    </div>
                </div>

                {{-- Submit Actions --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Publish</h3>
                    <div class="space-y-3">
                        <button type="submit" name="action" value="draft"
                                class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            Save as Draft
                        </button>
                        <button type="submit" name="action" value="submit"
                                class="w-full px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
                            Submit for Review
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function productForm() {
            return {
                productType: '{{ old('type', 'physical') }}',
                specFields: [],
                values: {},
                multiValues: {},
                previews: [],
                newFiles: [],
                featuredIndex: 0,
                altTexts: {},
                dragover: false,
                dragIndex: null,
                metaTitle: '{{ old('meta_title') }}',
                metaDesc: '{{ old('meta_description') }}',
                discountPercent: 0,

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

                    // Discount calculator
                    const priceEl = document.getElementById('price');
                    const compareEl = document.getElementById('compare_at_price');
                    const updateDiscount = () => {
                        const price = parseFloat(priceEl.value) || 0;
                        const compare = parseFloat(compareEl.value) || 0;
                        this.discountPercent = (compare > price && compare > 0)
                            ? Math.round((1 - price / compare) * 100) : 0;
                    };
                    priceEl.addEventListener('input', updateDiscount);
                    compareEl.addEventListener('input', updateDiscount);
                    updateDiscount();
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
                },

                handleFiles(files) {
                    Array.from(files).forEach(file => {
                        if (this.newFiles.length >= 10) return;
                        if (!file.type.startsWith('image/')) return;
                        if (file.size > 5 * 1024 * 1024) return;

                        this.newFiles.push(file);
                        const url = URL.createObjectURL(file);
                        this.previews.push({ url, type: 'new', file });
                        this.altTexts[this.previews.length - 1] = '';
                    });
                    this.syncFilesInput();
                },

                handleDrop(event) {
                    this.dragover = false;
                    const files = event.dataTransfer.files;
                    this.handleFiles(files);
                },

                removeNewImage(index) {
                    URL.revokeObjectURL(this.previews[index].url);
                    this.previews.splice(index, 1);
                    this.newFiles.splice(index, 1);
                    delete this.altTexts[index];
                    if (this.featuredIndex >= this.previews.length) {
                        this.featuredIndex = Math.max(0, this.previews.length - 1);
                    }
                    this.syncFilesInput();
                },

                syncFilesInput() {
                    const dt = new DataTransfer();
                    this.newFiles.forEach(f => dt.items.add(f));
                    this.$refs.fileInput.files = dt.files;
                },

                dragStart(event, index) {
                    this.dragIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                },

                dragOver(event, index) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                },

                drop(event, index) {
                    event.preventDefault();
                    if (this.dragIndex === null || this.dragIndex === index) return;
                    const item = this.previews.splice(this.dragIndex, 1)[0];
                    this.previews.splice(index, 0, item);
                    if (this.featuredIndex === this.dragIndex) {
                        this.featuredIndex = index;
                    } else if (this.dragIndex < this.featuredIndex && index >= this.featuredIndex) {
                        this.featuredIndex--;
                    } else if (this.dragIndex > this.featuredIndex && index <= this.featuredIndex) {
                        this.featuredIndex++;
                    }
                    this.dragIndex = null;
                }
            };
        }
    </script>
    @endpush
</x-seller.layout>
