<x-seller.layout title="My Products" active="products">
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

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Products</h2>
            <p class="text-sm text-gray-500 mt-1">Manage your product listings</p>
        </div>
        <a href="{{ route('seller.products.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('seller.products.index') }}" class="flex flex-col sm:flex-row gap-3 p-4 border-b border-gray-200">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
            <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
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
                                <a href="{{ route('seller.products.show', $product) }}" class="text-emerald-600 hover:text-emerald-900">View</a>
                                @if(in_array($product->status, ['draft', 'rejected']))
                                    <a href="{{ route('seller.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                @endif
                                @if(in_array($product->status, ['draft', 'rejected']))
                                    <form method="POST" action="{{ route('seller.products.submit', $product) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">Submit</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No products yet. Click "Add Product" to create your first product.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $products->links() }}
        </div>
    </div>
</x-seller.layout>
