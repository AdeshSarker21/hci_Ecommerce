<x-admin.layout title="Edit Product - {{ $product->name }}" active="products">
    <x-admin.page-header title="Edit Product">
        <x-slot:subtitle>{{ $product->name }}</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.products.index') }}" type="secondary">Back to List</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data"
          x-data="productForm()" x-init="init()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <x-admin.card title="Basic Information">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name (English) *</label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name (Bangla)</label>
                                <input type="text" name="name_bn" value="{{ old('name_bn', $product->name_bn) }}"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description (English)</label>
                            <textarea name="description" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">{{ old('description', $product->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Description (Bangla)</label>
                            <textarea name="description_bn" rows="4"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">{{ old('description_bn', $product->description_bn) }}</textarea>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Category, Brand & Type">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Seller</label>
                            <select name="seller_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="">Select Seller *</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ old('seller_id', $product->seller_id) == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->store_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Type *</label>
                            <select name="type" required x-model="productType"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="physical">Physical</option>
                                <option value="digital">Digital</option>
                                <option value="service">Service</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                            <select name="category_id" id="category_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label>
                            <select name="brand_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="">Select Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Price & Discount">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price *</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" required step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Compare at Price</label>
                            <input type="number" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cost Price</label>
                            <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="SKU & Identifiers">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200"
                                   placeholder="Auto-generated if empty">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Barcode</label>
                            <div class="flex items-center space-x-2">
                                @if($product->barcode)
                                    <span class="text-sm text-gray-500 font-mono">{{ $product->barcode }}</span>
                                    @if($product->barcode_url)
                                        <a href="{{ $product->barcode_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">View SVG</a>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Generated after save</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">QR Code</label>
                            <div class="flex items-center space-x-2">
                                @if($product->qr_code_url)
                                    <a href="{{ $product->qr_code_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline">View SVG</a>
                                @else
                                    <span class="text-xs text-gray-400">Generated after save</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Specifications" x-show="specFields.length > 0" x-transition>
                    <div class="space-y-4">
                        <template x-for="field in specFields" :key="field.id">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    <span x-text="field.name"></span>
                                    <span x-show="field.is_required" class="text-red-500">*</span>
                                </label>
                                <template x-if="field.type === 'text' || field.type === 'number'">
                                    <input :type="field.type === 'number' ? 'number' : 'text'"
                                           :name="'spec[' + field.id + ']'" x-model="values[field.id]"
                                           :required="field.is_required" :step="field.type === 'number' ? 'any' : undefined"
                                           class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                </template>
                                <template x-if="field.type === 'select'">
                                    <select :name="'spec[' + field.id + ']'" x-model="values[field.id]" :required="field.is_required"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                        <option value="">Select</option>
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <option :value="opt.value" x-text="opt.value"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="field.type === 'multiselect' || field.type === 'checkbox'">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="opt in field.values" :key="opt.id">
                                            <label class="flex items-center space-x-2 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-indigo-300 cursor-pointer"
                                                   :class="multiValues[field.id]?.includes(opt.value) ? 'border-indigo-500 bg-indigo-50' : ''">
                                                <input type="checkbox" :name="'spec[' + field.id + '][]'" :value="opt.value"
                                                       x-model="multiValues[field.id]" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700" x-text="opt.value"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="field.type === 'boolean' || field.type === 'radio'">
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" :name="'spec[' + field.id + ']'" value="1" x-model="values[field.id]" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700">Yes</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" :name="'spec[' + field.id + ']'" value="0" x-model="values[field.id]" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700">No</span>
                                        </label>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </x-admin.card>

                <x-admin.card title="Product Images">
                    <div class="space-y-4">
                        @if($product->images->count() > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach($product->images as $img)
                                    <div class="relative group rounded-lg overflow-hidden border-2 {{ $img->is_featured ? 'border-indigo-500' : 'border-gray-200' }}"
                                         data-image-id="{{ $img->id }}">
                                        <img src="{{ $img->full_url }}" class="w-full aspect-square object-cover" alt="{{ $img->alt_text }}">
                                        <div class="absolute top-1 left-1" x-show="{{ $img->is_featured ? 'true' : 'false' }}">
                                            <span class="px-1.5 py-0.5 bg-indigo-500 text-white text-xs font-medium rounded">Featured</span>
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

                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition-colors"
                             x-on:click="$refs.fileInput.click()"
                             x-on:dragover.prevent="dragover = true"
                             x-on:dragleave.prevent="dragover = false"
                             x-on:drop.prevent="handleDrop($event)"
                             :class="dragover ? 'border-indigo-400 bg-indigo-50' : ''">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">Drag and drop images, or <span class="text-indigo-600 font-medium">browse</span></p>
                            <p class="mt-1 text-xs text-gray-400">Max 10 images. JPEG, PNG, GIF, WebP up to 5MB.</p>
                        </div>
                        <input type="file" name="images[]" x-ref="fileInput" multiple accept="image/*" class="hidden" x-on:change="handleFiles($event.target.files)">
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
                </x-admin.card>

                <x-admin.card title="Inventory" x-show="productType === 'physical'" x-transition>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $product->quantity) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center pb-2.5">
                                <input type="checkbox" name="manage_stock" value="1" {{ old('manage_stock', $product->manage_stock) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Track Stock</span>
                            </label>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Shipping" x-show="productType === 'physical'" x-transition>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Weight (kg)</label>
                            <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Shipping Class</label>
                            <select name="shipping_class"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                                <option value="">Standard</option>
                                <option value="heavy" {{ old('shipping_class', $product->shipping_class) === 'heavy' ? 'selected' : '' }}>Heavy</option>
                                <option value="fragile" {{ old('shipping_class', $product->shipping_class) === 'fragile' ? 'selected' : '' }}>Fragile</option>
                                <option value="free" {{ old('shipping_class', $product->shipping_class) === 'free' ? 'selected' : '' }}>Free Shipping</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Length (cm)</label>
                            <input type="number" name="length" value="{{ old('length', $product->length) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Width (cm)</label>
                            <input type="number" name="width" value="{{ old('width', $product->width) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Height (cm)</label>
                            <input type="number" name="height" value="{{ old('height', $product->height) }}" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="SEO">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Meta Description</label>
                            <textarea name="meta_description" rows="3"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200">{{ old('meta_description', $product->meta_description) }}</textarea>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <div class="space-y-6">
                <x-admin.card title="Status">
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Featured</span>
                        </label>
                    </div>
                </x-admin.card>

                <x-admin.card title="Save">
                    <div class="space-y-3">
                        <button type="submit" name="action" value="draft"
                                class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200">
                            Save as Draft
                        </button>
                        <button type="submit" name="action" value="submit"
                                class="w-full px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            Update Product
                        </button>
                    </div>
                </x-admin.card>
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

                init() {
                    const categoryId = document.getElementById('category_id');
                    if (categoryId?.value) this.loadSpecs(categoryId.value);
                    categoryId?.addEventListener('change', (e) => {
                        if (e.target.value) this.loadSpecs(e.target.value);
                        else { this.specFields = []; this.values = {}; this.multiValues = {}; }
                    });
                },

                async loadSpecs(categoryId) {
                    try {
                        const response = await fetch(`/admin/category-attributes/${categoryId}/attributes`, {
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
                    } catch (e) { console.error('Failed to load specs:', e); }
                },

                handleFiles(files) {
                    Array.from(files).forEach(file => {
                        if (this.newFiles.length >= 10 || !file.type.startsWith('image/') || file.size > 5*1024*1024) return;
                        this.newFiles.push(file);
                        this.previews.push(URL.createObjectURL(file));
                    });
                    this.syncFilesInput();
                },

                handleDrop(e) { this.dragover = false; this.handleFiles(e.dataTransfer.files); },

                removeNewImage(index) {
                    URL.revokeObjectURL(this.previews[index]);
                    this.previews.splice(index, 1);
                    this.newFiles.splice(index, 1);
                    if (this.featuredIndex >= this.previews.length) this.featuredIndex = Math.max(0, this.previews.length - 1);
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
</x-admin.layout>
