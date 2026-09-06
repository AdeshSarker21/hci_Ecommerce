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

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <a href="{{ route('seller.products.index') }}" class="bg-white rounded-xl shadow-sm border {{ !request('status') ? 'border-emerald-300 ring-1 ring-emerald-500' : 'border-gray-200' }} p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500">Total</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'published']) }}" class="bg-white rounded-xl shadow-sm border {{ request('status') === 'published' ? 'border-green-300 ring-1 ring-green-500' : 'border-gray-200' }} p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500">Published</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ $stats['published'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'draft']) }}" class="bg-white rounded-xl shadow-sm border {{ request('status') === 'draft' ? 'border-gray-300 ring-1 ring-gray-500' : 'border-gray-200' }} p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500">Draft</p>
            <p class="text-xl font-bold text-gray-600 mt-1">{{ $stats['draft'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'pending_review']) }}" class="bg-white rounded-xl shadow-sm border {{ request('status') === 'pending_review' ? 'border-yellow-300 ring-1 ring-yellow-500' : 'border-gray-200' }} p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500">Pending</p>
            <p class="text-xl font-bold text-yellow-600 mt-1">{{ $stats['pending_review'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'approved']) }}" class="bg-white rounded-xl shadow-sm border {{ request('status') === 'approved' ? 'border-blue-300 ring-1 ring-blue-500' : 'border-gray-200' }} p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500">Approved</p>
            <p class="text-xl font-bold text-blue-600 mt-1">{{ $stats['approved'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'rejected']) }}" class="bg-white rounded-xl shadow-sm border {{ request('status') === 'rejected' ? 'border-red-300 ring-1 ring-red-500' : 'border-gray-200' }} p-4 hover:shadow-md transition-shadow">
            <p class="text-xs font-medium text-gray-500">Rejected</p>
            <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['rejected'] }}</p>
        </a>
    </div>

    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">My Products</h2>
            <p class="text-sm text-gray-500 mt-1">Manage your product listings</p>
        </div>
        <a href="{{ route('seller.products.create') }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        {{-- Filters --}}
        <form method="GET" action="{{ route('seller.products.index') }}" class="p-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU..."
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm pl-10">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <select name="category_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <select name="type" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">All Types</option>
                    <option value="physical" {{ request('type') === 'physical' ? 'selected' : '' }}>Physical</option>
                    <option value="digital" {{ request('type') === 'digital' ? 'selected' : '' }}>Digital</option>
                    <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Service</option>
                </select>
                <select name="sort" onchange="this.form.submit()" class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                    <option value="stock_asc" {{ request('sort') === 'stock_asc' ? 'selected' : '' }}>Stock: Low</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">Search</button>
            </div>
        </form>

        {{-- Product Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                        @if($product->images->first())
                                            <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate max-w-[200px]">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $product->sku }} @if($product->category) | {{ $product->category->name }} @endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $product->type_badge }}">{{ ucfirst($product->type) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                    @if($product->compare_at_price && $product->compare_at_price > $product->price)
                                        <span class="text-xs text-gray-400 line-through block">${{ number_format($product->compare_at_price, 2) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $product->stock_badge }}">
                                    {{ $product->stock_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $product->status_badge }}">
                                    {{ str_replace('_', ' ', ucfirst($product->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1" x-data="{ open: false }">
                                    <a href="{{ route('seller.products.show', $product) }}"
                                       class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @if(in_array($product->status, ['draft', 'rejected', 'pending_review']))
                                        <a href="{{ route('seller.products.edit', $product) }}"
                                           class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif

                                    @if(in_array($product->status, ['draft', 'rejected']))
                                        <form method="POST" action="{{ route('seller.products.duplicate', $product) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Duplicate">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($product->status, ['draft', 'rejected']))
                                        <form method="POST" action="{{ route('seller.products.submit', $product) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Submit for Review">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if($product->status === 'approved')
                                        <form method="POST" action="{{ route('seller.products.publish', $product) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Publish">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if($product->status === 'published')
                                        <form method="POST" action="{{ route('seller.products.unpublish', $product) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Unpublish">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if(!in_array($product->status, ['published']))
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors" title="More actions">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                 class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg py-1 ring-1 ring-black ring-opacity-5 z-10">
                                                @if(!in_array($product->status, ['published']))
                                                    <form method="POST" action="{{ route('seller.products.destroy', $product) }}"
                                                          x-data="{ confirm: false }"
                                                          @submit.prevent="if(!confirm) { confirm = true; if(!confirm('Delete this product? This action cannot be undone.')) { confirm = false; return false; } $el.submit(); }">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No products yet</h3>
                                <p class="text-sm text-gray-500 mb-4">Get started by creating your first product.</p>
                                <a href="{{ route('seller.products.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Your First Product
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $products->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-seller.layout>
