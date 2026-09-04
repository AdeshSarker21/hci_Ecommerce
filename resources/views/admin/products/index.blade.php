<x-admin.layout title="Products" active="products">
    <x-admin.page-header title="Products">
        <x-slot:subtitle>Manage marketplace products</x-slot:subtitle>
    </x-admin.page-header>

    <x-admin.alert />

    <x-admin.card>
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU, or store..."
                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <x-admin.button type="submit" size="md">Filter</x-admin.button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->seller->store_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${{ number_format($product->price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock_badge }}">
                                    {{ $product->stock_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->status_badge }}">
                                    {{ str_replace('_', ' ', ucfirst($product->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                @if($product->isPendingReview())
                                    <form method="POST" action="{{ route('admin.products.approve', $product) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                    </form>
                                @endif
                                @if($product->isApproved())
                                    <form method="POST" action="{{ route('admin.products.publish', $product) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">Publish</button>
                                    </form>
                                @endif
                                @if($product->isPublished())
                                    <form method="POST" action="{{ route('admin.products.unpublish', $product) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-orange-600 hover:text-orange-900">Unpublish</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </x-admin.card>
</x-admin.layout>
