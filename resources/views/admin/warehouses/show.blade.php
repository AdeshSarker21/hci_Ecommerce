<x-admin.layout title="Warehouse - {{ $warehouse->name }}" active="warehouses">
    <x-admin.page-header title="Warehouse Details">
        <x-slot:subtitle>{{ $warehouse->name }}</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.warehouses.index') }}" type="secondary">Back to List</x-admin.button>
            <x-admin.button href="{{ route('admin.warehouses.edit', $warehouse) }}" type="primary">Edit</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <x-admin.card>
                <x-slot:title>Information</x-slot:title>
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
                        <dt class="text-xs font-medium text-gray-500 uppercase">Owner</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $warehouse->seller?->store_name ?? 'Admin / Marketplace' }}</dd>
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
            </x-admin.card>
        </div>

        <div class="lg:col-span-2">
            <x-admin.card>
                <x-slot:title>Products in this Warehouse</x-slot:title>
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
            </x-admin.card>
        </div>
    </div>
</x-admin.layout>
