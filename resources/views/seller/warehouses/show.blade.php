<x-seller.layout title="Warehouse - {{ $warehouse->name }}" active="warehouses">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $warehouse->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $warehouse->code }} - {{ $warehouse->full_address ?? 'No address' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('seller.warehouses.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                Back
            </a>
            <a href="{{ route('seller.warehouses.edit', $warehouse) }}" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                Edit
            </a>
            <form method="POST" action="{{ route('seller.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Delete this warehouse?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Name</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $warehouse->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Code</dt>
                        <dd class="text-sm font-mono text-gray-900 mt-1">{{ $warehouse->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Status</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $warehouse->status_badge }}">
                                {{ ucfirst($warehouse->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Address</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $warehouse->full_address ?? 'No address' }}</dd>
                    </div>
                    @if($warehouse->phone)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Phone</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $warehouse->phone }}</dd>
                        </div>
                    @endif
                    @if($warehouse->email)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Email</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $warehouse->email }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Products in this Warehouse</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reserved</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $product->sku }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $product->pivot->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $product->pivot->reserved_quantity }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold {{ ($product->pivot->quantity - $product->pivot->reserved_quantity) <= 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ max(0, $product->pivot->quantity - $product->pivot->reserved_quantity) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">No products in this warehouse.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</x-seller.layout>
