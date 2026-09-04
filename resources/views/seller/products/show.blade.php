<x-seller.layout title="{{ $product->name }}" active="products">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">Product details</p>
        </div>
        <div class="flex items-center space-x-3">
            @if(in_array($product->status, ['draft', 'rejected']))
                <a href="{{ route('seller.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Edit</a>
            @endif
            <a href="{{ route('seller.products.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name (English)</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->name }}</dd>
                    </div>
                    @if($product->name_bn)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name (Bangla)</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->name_bn }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">SKU</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->sku }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->type_badge }}">{{ ucfirst($product->type) }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->category?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Brand</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->brand?->name ?? '—' }}</dd>
                    </div>
                    @if($product->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing & Stock</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Price</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($product->price, 2) }}</dd>
                    </div>
                    @if($product->compare_at_price)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Compare at Price</dt>
                            <dd class="mt-1 text-sm text-gray-900">${{ number_format($product->compare_at_price, 2) }}</dd>
                        </div>
                    @endif
                    @if($product->discount_percentage)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Discount</dt>
                            <dd class="mt-1 text-sm text-green-600 font-semibold">{{ $product->discount_percentage }}% off</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Stock</dt>
                        <dd class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock_badge }}">{{ $product->stock_label }}</span></dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    <span class="block w-full text-center px-4 py-2 text-sm font-medium rounded-lg {{ $product->status_badge }}">
                        {{ str_replace('_', ' ', ucfirst($product->status)) }}
                    </span>
                    @if(in_array($product->status, ['draft', 'rejected']))
                        <form method="POST" action="{{ route('seller.products.submit', $product) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Submit for Review</button>
                        </form>
                        <a href="{{ route('seller.products.edit', $product) }}" class="block w-full text-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Edit Product</a>
                    @endif
                    @if(in_array($product->status, ['draft', 'rejected']))
                        <form method="POST" action="{{ route('seller.products.destroy', $product) }}" x-data="{ confirm: false }">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="confirm = true" class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete Product</button>
                            <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
                                <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-xl">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Delete Product?</h4>
                                    <p class="text-sm text-gray-600 mb-4">This action cannot be undone.</p>
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" @click="confirm = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                    @if($product->approved_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Approved</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->approved_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($product->published_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Published</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->published_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($product->rejection_reason)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                            <dd class="mt-1 p-2 bg-red-50 rounded text-sm text-red-700">{{ $product->rejection_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-seller.layout>
