<x-admin.layout title="Review Product" active="review">
    <x-admin.page-header title="{{ $product->name }}">
        <x-slot:subtitle>Review product for moderation</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.review.index') }}" type="secondary">Back to Queue</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Product Information">
                <div class="flex items-center space-x-6 mb-6">
                    <div class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden">
                        @if($product->primary_image)
                            <img src="{{ Storage::disk('public')->url($product->primary_image) }}" alt="{{ $product->name }}" class="w-20 h-20 object-cover">
                        @else
                            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $product->name }}</h3>
                        @if($product->name_bn)
                            <p class="text-gray-500">{{ $product->name_bn }}</p>
                        @endif
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->status_badge }}">{{ str_replace('_', ' ', ucfirst($product->status)) }}</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->type_badge }}">{{ ucfirst($product->type) }}</span>
                            @if($product->is_featured)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Featured</span>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Slug</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">SKU</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $product->sku }}</dd>
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
                    @if($product->description_bn)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Description (Bangla)</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $product->description_bn }}</dd>
                        </div>
                    @endif
                </dl>
            </x-admin.card>

            <x-admin.card title="Pricing & Stock">
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
                    @if($product->cost_price)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cost Price</dt>
                            <dd class="mt-1 text-sm text-gray-900">${{ number_format($product->cost_price, 2) }}</dd>
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
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock_badge }}">
                                {{ $product->stock_label }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </x-admin.card>

            @if($product->attributeValues->count())
                <x-admin.card title="Specifications">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($product->attributeValues as $attrValue)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ $attrValue->attribute->name }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $attrValue->display_value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-admin.card>
            @endif

            @if($product->images->count())
                <x-admin.card title="Images">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($product->images as $image)
                            <div class="relative group">
                                <img src="{{ Storage::disk('public')->url($image->path) }}" alt="{{ $image->alt_text }}"
                                     class="w-full h-32 object-cover rounded-lg border border-gray-200">
                                @if($image->is_featured)
                                    <span class="absolute top-1 left-1 px-2 py-0.5 text-xs font-semibold rounded bg-indigo-600 text-white">Featured</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif

            @if($product->meta_title || $product->meta_description)
                <x-admin.card title="SEO">
                    <dl class="space-y-3">
                        @if($product->meta_title)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Meta Title</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $product->meta_title }}</dd>
                            </div>
                        @endif
                        @if($product->meta_description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Meta Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $product->meta_description }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-admin.card>
            @endif
        </div>

        <div class="space-y-6">
            <x-admin.card title="Moderation Actions">
                <div class="space-y-4">
                    @if($product->isPendingReview())
                        <form method="POST" action="{{ route('admin.review.approve', $product) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="approve_reason" class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                                <textarea name="reason" id="approve_reason" rows="2"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200"
                                          placeholder="Optional note for the seller..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                Approve Product
                            </button>
                        </form>

                        <hr class="border-gray-200">

                        <form method="POST" action="{{ route('admin.review.reject', $product) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200"
                                          placeholder="Explain why this product is being rejected..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                Reject Product
                            </button>
                        </form>
                    @elseif($product->isApproved())
                        <form method="POST" action="{{ route('admin.review.publish', $product) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                Publish Product
                            </button>
                        </form>

                        <hr class="border-gray-200">

                        <form method="POST" action="{{ route('admin.review.reject', $product) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason *</label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" required
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200"
                                          placeholder="Explain why this product is being rejected..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                                Reject Product
                            </button>
                        </form>
                    @elseif($product->isPublished())
                        <form method="POST" action="{{ route('admin.review.unpublish', $product) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                                Unpublish Product
                            </button>
                        </form>
                    @elseif($product->isRejected())
                        <form method="POST" action="{{ route('admin.review.approve', $product) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="approve_reason" class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                                <textarea name="reason" id="approve_reason" rows="2"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors duration-200"
                                          placeholder="Optional note for the seller..."></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                Approve Product
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500 text-center py-2">No actions available for this status.</p>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card title="Seller">
                <div class="flex items-center p-2">
                    <img src="{{ $product->seller->logo_url }}" alt="{{ $product->seller->store_name }}" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $product->seller->store_name }}</p>
                        <p class="text-xs text-gray-500">{{ $product->seller->user->name }}</p>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Details">
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
            </x-admin.card>

            @if($product->moderations->count())
                <x-admin.card title="Moderation History">
                    <div class="space-y-4">
                        @foreach($product->moderations as $moderation)
                            <div class="flex items-start space-x-3 {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">
                                <div class="flex-shrink-0 mt-0.5">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $moderation->action_badge }}">
                                        {{ $moderation->action_label }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        by <span class="font-medium">{{ $moderation->reviewer->name }}</span>
                                    </p>
                                    @if($moderation->reason)
                                        <p class="text-sm text-gray-600 mt-0.5">{{ $moderation->reason }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">{{ $moderation->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>
    </div>
</x-admin.layout>
