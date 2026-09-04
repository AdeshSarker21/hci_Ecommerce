<x-admin.layout title="Permissions" active="permissions">
    <x-admin.page-header title="Permissions">
        <x-slot:subtitle>View all system permissions grouped by category</x-slot:subtitle>
    </x-admin.page-header>

    <div class="space-y-6">
        @forelse($permissions as $group => $items)
            <x-admin.card>
                <x-slot:title>
                    <span class="capitalize">{{ $group }}</span>
                    <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ $items->count() }}</span>
                </x-slot:title>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($items as $permission)
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $permission->name }}</p>
                                <p class="text-xs text-gray-500">{{ $permission->slug }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @empty
            <x-admin.card>
                <p class="text-sm text-gray-500 text-center py-8">No permissions found.</p>
            </x-admin.card>
        @endforelse
    </div>
</x-admin.layout>
