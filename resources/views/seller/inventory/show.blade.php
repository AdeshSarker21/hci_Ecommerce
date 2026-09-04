<x-seller.layout title="Inventory - {{ $product->name }}" active="inventory">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Inventory Management</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $product->name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('seller.inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Back</a>
            <a href="{{ route('seller.inventory.print', $product) }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50" target="_blank">Print Labels</a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Product Info & Stock --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">SKU</dt>
                        <dd class="text-sm font-mono text-gray-900 mt-1">{{ $product->sku }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Barcode</dt>
                        <dd class="text-sm font-mono text-gray-900 mt-1">{{ $product->barcode ?? 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Category</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $product->category?->name ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Stock Levels</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Total Stock</span>
                        <span class="text-lg font-bold text-gray-900">{{ $product->quantity }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Reserved</span>
                        <span class="text-lg font-bold text-purple-600">{{ $product->reserved_quantity }}</span>
                    </div>
                    <div class="border-t pt-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Available</span>
                        <span class="text-lg font-bold {{ $product->available_stock <= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $product->available_stock === PHP_INT_MAX ? '∞' : $product->available_stock }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Low Stock Threshold</span>
                        <span class="text-sm text-gray-700">{{ $product->low_stock_threshold }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->stock_badge }}">
                            {{ $product->stock_label }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Identifiers</h3>
                <div class="space-y-3">
                    <form method="POST" action="{{ route('seller.inventory.generate-barcode', $product) }}" class="flex items-center justify-between">
                        @csrf
                        <span class="text-sm text-gray-500">Barcode</span>
                        <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-900 font-medium">Generate</button>
                    </form>
                    <form method="POST" action="{{ route('seller.inventory.generate-qr', $product) }}" class="flex items-center justify-between">
                        @csrf
                        <span class="text-sm text-gray-500">QR Code</span>
                        <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-900 font-medium">Generate</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Stock Adjustment & History --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Stock</h3>
                <form method="POST" action="{{ route('seller.inventory.add-stock', $product) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                            <input type="number" name="quantity" min="1" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit Cost</label>
                            <input type="number" name="unit_cost" step="0.01" min="0"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                            <input type="text" name="notes" placeholder="Reason"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">Add Stock</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Remove Stock</h3>
                <form method="POST" action="{{ route('seller.inventory.remove-stock', $product) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                            <input type="number" name="quantity" min="1" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Type *</label>
                            <select name="type" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                                <option value="sale">Sale</option>
                                <option value="return">Return</option>
                                <option value="cancellation">Cancellation</option>
                                <option value="damaged">Damaged</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                            <input type="text" name="notes" placeholder="Reason"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">Remove Stock</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Set Stock Level</h3>
                <form method="POST" action="{{ route('seller.inventory.adjust', $product) }}">
                    @csrf
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Quantity *</label>
                            <input type="number" name="quantity" min="0" required value="{{ $product->quantity }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                            <input type="text" name="notes" placeholder="Reason for adjustment"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">Set Level</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction History</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Before</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">After</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">{{ $tx->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tx->type_badge }}">
                                            {{ $tx->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold {{ $tx->quantity > 0 ? 'text-green-600' : ($tx->quantity < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                        {{ $tx->quantity > 0 ? '+' : '' }}{{ $tx->quantity }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $tx->quantity_before }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $tx->quantity_after }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $tx->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">No transactions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-seller.layout>
