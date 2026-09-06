<x-admin.layout title="Commission Rules" active="commission">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Commission Rules</h2>
            <p class="text-sm text-gray-500 mt-1">Configure commission rates for sellers, categories and products</p>
        </div>
        <button onclick="document.getElementById('addRuleModal').classList.remove('hidden')" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">
            Add Rule
        </button>
    </div>

    {{-- Rules Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($rules->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applies To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($rules as $rule)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $rule->name }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $rule->type_badge }}">{{ ucfirst($rule->type) }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $rule->value }}{{ $rule->type === 'percentage' ? '%' : '' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $rule->applies_to_label }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $rule->priority }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <form method="POST" action="{{ route('admin.commission.rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Delete this rule?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $rules->links() }}</div>
        @else
            <div class="px-6 py-16 text-center">
                <p class="text-gray-500">No commission rules configured. The default rate will apply.</p>
            </div>
        @endif
    </div>

    {{-- Add Rule Modal --}}
    <div id="addRuleModal" class="hidden fixed inset-0 z-50 bg-gray-900/50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Commission Rule</h3>
            <form method="POST" action="{{ route('admin.commission.rules.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required class="w-full mt-1 text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="type" required class="w-full mt-1 text-sm border-gray-300 rounded-lg">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Value</label>
                            <input type="number" name="value" step="0.01" min="0" required class="w-full mt-1 text-sm border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Applies To</label>
                        <select name="applies_to" required class="w-full mt-1 text-sm border-gray-300 rounded-lg">
                            <option value="global">Global (All Sellers)</option>
                            <option value="seller">Specific Seller</option>
                            <option value="category">Specific Category</option>
                            <option value="product">Specific Product</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priority (higher = checked first)</label>
                        <input type="number" name="priority" value="0" class="w-full mt-1 text-sm border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="2" class="w-full mt-1 text-sm border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addRuleModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Create Rule</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layout>
