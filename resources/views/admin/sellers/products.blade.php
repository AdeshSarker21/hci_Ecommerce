<x-admin.layout title="Products - {{ $seller->store_name }}" active="sellers">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('admin.sellers.show', $seller) }}" class="hover:text-indigo-600">{{ $seller->store_name }}</a>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-gray-900 font-medium">Products</span>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <select name="status" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Filter</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if($products->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">${{ number_format($product->price, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                            @if($product->status === 'published') bg-green-100 text-green-800
                                            @elseif($product->status === 'pending_review') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $product->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">{{ $products->links() }}</div>
            @else
                <div class="px-6 py-12 text-center"><p class="text-gray-500">No products found</p></div>
            @endif
        </div>
    </div>
</x-admin.layout>
