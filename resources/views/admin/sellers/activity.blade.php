<x-admin.layout title="Activity Log - {{ $seller->store_name }}" active="sellers">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.sellers.show', $seller) }}" class="hover:text-indigo-600">{{ $seller->store_name }}</a>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-medium">Activity Log</span>
            </div>
            <a href="{{ route('admin.sellers.show', $seller) }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Back to Seller</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200">
            @if($activities->count())
                <div class="divide-y divide-gray-100">
                    @foreach($activities as $log)
                        <div class="px-6 py-4 flex items-start space-x-4">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                @if(str_contains($log->type, 'approved'))
                                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif(str_contains($log->type, 'rejected') || str_contains($log->type, 'suspended'))
                                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @elseif(str_contains($log->type, 'activated'))
                                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">{{ $log->description }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-500">by {{ $log->user?->name ?? 'System' }}</span>
                                    <span class="text-xs text-gray-400">&middot;</span>
                                    <span class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                @if($log->properties && count($log->properties))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($log->properties as $key => $value)
                                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $key }}: {{ is_array($value) ? json_encode($value) : $value }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600 whitespace-nowrap">{{ $log->type }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-gray-200">{{ $activities->links() }}</div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-gray-500">No activity recorded yet</p>
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
