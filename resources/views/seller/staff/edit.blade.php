<x-seller.layout title="Edit Staff Member" active="staff">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Staff Member</h2>
            <p class="text-sm text-gray-500 mt-1">Update {{ $staff->name }}'s information and permissions</p>
        </div>
        <a href="{{ route('seller.staff.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
            Back to Staff
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-medium text-red-700">Please fix the following errors:</p>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('seller.staff.update', $staff) }}">
        @csrf
        @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Staff Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select name="role" id="role" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="manager" {{ old('role', $staff->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="product-manager" {{ old('role', $staff->role) === 'product-manager' ? 'selected' : '' }}>Product Manager</option>
                        <option value="order-manager" {{ old('role', $staff->role) === 'order-manager' ? 'selected' : '' }}>Order Manager</option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('can_manage_products', $staff->can_manage_products) ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200' }}">
                        <input type="checkbox" name="can_manage_products" value="1"
                               {{ old('can_manage_products', $staff->can_manage_products) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-gray-700">Manage Products</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('can_manage_orders', $staff->can_manage_orders) ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200' }}">
                        <input type="checkbox" name="can_manage_orders" value="1"
                               {{ old('can_manage_orders', $staff->can_manage_orders) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-gray-700">Manage Orders</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('can_manage_settings', $staff->can_manage_settings) ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200' }}">
                        <input type="checkbox" name="can_manage_settings" value="1"
                               {{ old('can_manage_settings', $staff->can_manage_settings) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-gray-700">Manage Settings</span>
                    </label>
                    <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('can_view_reports', $staff->can_view_reports) ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200' }}">
                        <input type="checkbox" name="can_view_reports" value="1"
                               {{ old('can_view_reports', $staff->can_view_reports) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="ml-2 text-sm text-gray-700">View Reports</span>
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('notes', $staff->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('seller.staff.index') }}" class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                Update Staff
            </button>
        </div>
    </form>
</x-seller.layout>
