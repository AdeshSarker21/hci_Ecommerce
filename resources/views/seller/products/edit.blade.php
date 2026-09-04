<x-seller.layout title="Edit Product" active="products">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Product</h2>
            <p class="text-sm text-gray-500 mt-1">Update "{{ $product->name }}"</p>
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

    <form method="POST" action="{{ route('seller.products.update', $product) }}" enctype="multipart/form-data"
          x-data="productForm()" x-init="init()" id="productForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ========== LEFT COLUMN (2/3) ========== --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Section 1: Basic Information --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name (English) *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">Product Name (Bangla)</label>
                            <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $product->name_bn) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                            <textarea name="description" id="description" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">{{ old('description', $product->description) }}</textarea>
                        </div>
                        <div>
                            <label for="description_bn" class="block text-sm font-medium text-gray-700 mb-1">Description (Bangla)</label>
                            <textarea name="description_bn" id="description_bn" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">{{ old('description_bn', $product->description_bn) }}</textarea>
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
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <select name="brand_id" id="brand_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
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
                                <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" required step="0.01" min="0"
                                       class="w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                            </div>
                        </div>
                        <div>
                            <label for="compare_at_price" class="block text-sm font-medium text-gray-700 mb-1">Compare at Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-gray-500">$</span>
                                <input type="number" name="compare_at_price" id="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" step="0.01" min="0"
                                       class="w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                            </div>
                        </div>
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-gray-500">$</span>
                                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"
                                       class="w-full rounded-lg border border-gray-300 bg-white pl-7 pr-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                            </div>
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500 font-mono">{{ $product->barcode }}</span>
                                @if($product->barcode_url)
                                    <a href="{{ $product->barcode_url }}" target="_blank" class="text-xs text-emerald-600 hover:underline">View SVG</a>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">QR Code</label>
                            <div class="flex items-center space-x-2">
                                @if($product->qr_code_url)
                                    <a href="{{ $product->qr_code_url }}" target="_blank" class="text-xs text-emerald-600 hover:underline">View SVG</a>
                                @else
                                    <span class="text-xs text-gray-400">Generated after save</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 5: Specifications --}}
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
                                <template x-if="field.type === 'text' || field.type === 'number'">
                                    <input :type="field.type === 'number' ? 'number' : 'text'"
                                           :name="'spec[' + field.id + ']'"
                                           x-model="values[field.id]"
                                           :required="field.is_required"
                                           :step="field.type === 'number' ? 'any' : undefined"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                </template>
                                <template x-if="field.type === 'textarea'">
                                    <textarea :name="'spec[' + field.id + ']'"
                                              x-model="values[field.id]"
                                              :required="field.is_required"
                                              rows="3"
                                              class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"></textarea>
                                </template>
                                <template x-if="field.type === 'select'">
                                    <select :name="'spec[' + field.id + ']'"
                                            x-model="values[field.id]"
                                            :required="field.is_required"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                        <option value="">Select</option>
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <option :value="opt.value" x-text="opt.value_bn ? opt.value + ' (' + opt.value_bn + ')' : opt.value"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="field.type === 'multiselect' || field.type === 'checkbox'">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <label class="flex items-center space-x-2 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-emerald-300 cursor-pointer transition-colors"
                                                   :class="multiValues[field.id]?.includes(opt.value) ? 'border-emerald-500 bg-emerald-50' : ''">
                                                <input type="checkbox" :name="'spec[' + field.id + '][]'" :value="opt.value"
                                                       x-model="multiValues[field.id]" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                                <span class="text-sm text-gray-700" x-text="opt.value_bn ? opt.value + ' (' + opt.value_bn + ')' : opt.value"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="field.type === 'boolean' || field.type === 'radio'">
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" :name="'spec[' + field.id + ']'" value="1" x-model="values[field.id]" class="border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-gray-700">Yes</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" :name="'spec[' + field.id + ']'" value="0" x-model="values[field.id]" class="border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm text-gray-700">No</span>
                                        </label>
                                    </div>
                                </template>
                                <template x-if="field.type === 'color'">
                                    <div class="flex items-center space-x-3">
                                        <input type="color" :name="'spec[' + field.id + ']'" x-model="values[field.id]" class="h-10 w-10 rounded-lg border border-gray-300 cursor-pointer">
                                        <input type="text" :name="'spec[' + field.id + ']'" x-model="values[field.id]"
                                               class="flex-1 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200" placeholder="#000000">
                                    </div>
                                </template>
                                <template x-if="field.type === 'date'">
                                    <input type="date" :name="'spec[' + field.id + ']'" x-model="values[field.id]" :required="field.is_required"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Section 6: Product Images --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Images</h3>
                    <div class="space-y-4">
                        {{-- Existing Images --}}
                        @if($product->images->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach($product->images as $img)
                                    <div class="relative group rounded-lg overflow-hidden border-2 {{ $img->is_featured ? 'border-emerald-500' : 'border-gray-200' }}"
                                         data-image-id="{{ $img->id }}">
                                        <img src="{{ $img->full_url }}" class="w-full aspect-square object-cover" alt="{{ $img->alt_text }}">
                                        <div class="absolute top-1 left-1" x-show="{{ $img->is_featured ? 'true' : 'false' }}">
                                            <span class="px-1.5 py-0.5 bg-emerald-500 text-white text-xs font-medium rounded">Featured</span>
                                        </div>
                                        <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" name="delete_images[]" value="{{ $img->id }}"
                                                       class="sr-only peer">
                                                <span class="p-1 rounded-full bg-white/80 hover:bg-white text-red-500 peer-checked:bg-red-500 peer-checked:text-white transition-colors">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                                            <input type="text" name="image_alt_texts[]" value="{{ $img->alt_text }}"
                                                   class="w-full text-xs bg-transparent text-white placeholder-white/70 border-0 p-0 focus:ring-0"
                                                   placeholder="Alt text">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="existing_images[]" value="{{ implode(',', $product->images->pluck('id')->toArray()) }}">
                        @endif

                        {{-- Upload New --}}
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-400 transition-colors"
                             x-on:click="$refs.fileInput.click()"
                             x-on:dragover.prevent="dragover = true"
                             x-on:dragleave.prevent="dragover = false"
                             x-on:drop.prevent="handleDrop($event)"
                             :class="dragover ? 'border-emerald-400 bg-emerald-50' : ''">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">Drag and drop more images here, or <span class="text-emerald-600 font-medium">browse</span></p>
                            <p class="mt-1 text-xs text-gray-400">Max 10 images total. JPEG, PNG, GIF, WebP up to 5MB.</p>
                        </div>
                        <input type="file" name="images[]" x-ref="fileInput" multiple accept="image/*" class="hidden" x-on:change="handleFiles($event.target.files)">

                        {{-- New Image Previews --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" x-show="previews.length > 0">
                            <template x-for="(preview, index) in previews" :key="'new-'+index">
                                <div class="relative group rounded-lg overflow-hidden border-2 border-gray-200">
                                    <img :src="preview" class="w-full aspect-square object-cover">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <button type="button" x-on:click="removeNewImage(index)"
                                                class="p-1.5 rounded-full bg-white/80 hover:bg-white text-red-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                                        <input type="text" :name="'image_alt_texts[' + ({{ $product->images->count() }} + index) + ']'"
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
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity', $product->quantity) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert At</label>
                            <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center pb-2.5">
                                <input type="checkbox" name="manage_stock" value="1" {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}
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
                            <input type="number" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="shipping_class" class="block text-sm font-medium text-gray-700 mb-1">Shipping Class</label>
                            <select name="shipping_class" id="shipping_class"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="">Standard</option>
                                <option value="heavy" {{ old('shipping_class', $product->shipping_class) === 'heavy' ? 'selected' : '' }}>Heavy/Bulky</option>
                                <option value="fragile" {{ old('shipping_class', $product->shipping_class) === 'fragile' ? 'selected' : '' }}>Fragile</option>
                                <option value="free" {{ old('shipping_class', $product->shipping_class) === 'free' ? 'selected' : '' }}>Free Shipping</option>
                            </select>
                        </div>
                        <div>
                            <label for="length" class="block text-sm font-medium text-gray-700 mb-1">Length (cm)</label>
                            <input type="number" name="length" id="length" value="{{ old('length', $product->length) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="width" class="block text-sm font-medium text-gray-700 mb-1">Width (cm)</label>
                            <input type="number" name="width" id="width" value="{{ old('width', $product->width) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                            <input type="number" name="height" id="height" value="{{ old('height', $product->height) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                    </div>
                </div>

                {{-- Section 9: SEO --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200" x-model="metaTitle">
                            <p class="mt-1 text-xs text-gray-400" x-text="metaTitle.length + '/60 characters'"></p>
                        </div>
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" rows="3"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200" x-model="metaDesc"></textarea>
                            <p class="mt-1 text-xs text-gray-400" x-text="metaDesc.length + '/160 characters'"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========== RIGHT COLUMN (1/3) ========== --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Visibility</h3>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-gray-700">Featured</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
                            Update Product
                        </button>
                        @if(in_array($product->status, ['draft', 'rejected']))
                            <a href="{{ route('seller.products.submit', $product) }}"
                               onclick="event.preventDefault(); document.getElementById('submit-form').submit();"
                               class="block w-full text-center px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                Submit for Review
                            </a>
                            <form id="submit-form" method="POST" action="{{ route('seller.products.submit', $product) }}" class="hidden">
                                @csrf
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function productForm() {
            return {
                productType: '{{ old('type', $product->type) }}',
                specFields: [],
                values: {},
                multiValues: {},
                previews: [],
                newFiles: [],
                featuredIndex: {{ $product->images->where('is_featured', true)->first()?->id ? 'null' : '0' }},
                dragover: false,
                metaTitle: '{{ old('meta_title', $product->meta_title) }}',
                metaDesc: '{{ old('meta_description', $product->meta_description) }}',
                discountPercent: {{ $product->discount_percentage ?? 0 }},

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
                },

                async loadSpecs(categoryId) {
                    try {
                        const response = await fetch(`/seller/category-attributes/${categoryId}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (response.ok) {
                            const newFields = await response.json();
                            const oldValues = { ...this.values };
                            const oldMultiValues = { ...this.multiValues };
                            this.specFields = newFields;
                            this.specFields.forEach(field => {
                                if (['multiselect', 'checkbox'].includes(field.type)) {
                                    this.multiValues[field.id] = oldMultiValues[field.id] || [];
                                } else {
                                    this.values[field.id] = oldValues[field.id] || '';
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Failed to load specs:', e);
                    }
                },

                handleFiles(files) {
                    Array.from(files).forEach(file => {
                        if (!file.type.startsWith('image/') || file.size > 5 * 1024 * 1024) return;
                        this.newFiles.push(file);
                        this.previews.push(URL.createObjectURL(file));
                    });
                    this.syncFilesInput();
                },

                handleDrop(event) {
                    this.dragover = false;
                    this.handleFiles(event.dataTransfer.files);
                },

                removeNewImage(index) {
                    URL.revokeObjectURL(this.previews[index]);
                    this.previews.splice(index, 1);
                    this.newFiles.splice(index, 1);
                    this.syncFilesInput();
                },

                syncFilesInput() {
                    const dt = new DataTransfer();
                    this.newFiles.forEach(f => dt.items.add(f));
                    this.$refs.fileInput.files = dt.files;
                }
            };
        }
    </script>
    @endpush
</x-seller.layout>
