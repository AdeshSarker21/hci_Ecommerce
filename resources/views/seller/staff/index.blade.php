<x-seller.layout title="Staff Management" active="staff">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Staff Members</h2>
            <p class="text-sm text-gray-500 mt-1">Manage your store staff and their permissions</p>
        </div>
        <a href="{{ route('seller.staff.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Staff
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($staff as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($member->user)
                                        <img src="{{ $member->user->avatar_url }}" alt="{{ $member->name }}" class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-600">{{ strtoupper(substr($member->name, 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @if($member->can_manage_products)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">Products</span>
                                    @endif
                                    @if($member->can_manage_orders)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800">Orders</span>
                                    @endif
                                    @if($member->can_manage_settings)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Settings</span>
                                    @endif
                                    @if($member->can_view_reports)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">Reports</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $member->status_badge }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $member->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <a href="{{ route('seller.staff.show', $member) }}" class="text-emerald-600 hover:text-emerald-900">View</a>
                                <a href="{{ route('seller.staff.edit', $member) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="POST" action="{{ route('seller.staff.toggle-status', $member) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="{{ $member->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                        {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No staff members yet. Click "Add Staff" to invite your first team member.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-seller.layout>
