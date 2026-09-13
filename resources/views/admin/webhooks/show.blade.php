<x-admin.layout title="Webhook Event #{{ $log->id }}" active="courier-webhooks">
    <div class="mb-6">
        <a href="{{ route('admin.courier-webhooks.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Webhook Logs
        </a>
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Webhook Event #{{ $log->id }}</h2>
                <p class="text-sm text-gray-500 mt-1">Received {{ $log->created_at->format('M d, Y h:i:s A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($log->is_processed)
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Processed</span>
                @elseif($log->error_message)
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                @else
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                @endif
                @if(!$log->is_processed)
                    <form method="POST" action="{{ route('admin.courier-webhooks.retry', $log) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200">Retry</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.courier-webhooks.destroy', $log) }}" onsubmit="return confirm('Delete this webhook log?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Courier</p><p class="text-sm font-semibold text-gray-900">{{ $log->courier?->name ?? $log->courier_slug }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Event Type</p><p class="text-sm font-semibold text-gray-900">{{ $log->event_type ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Consignment ID</p><p class="text-sm font-mono text-gray-900">{{ $log->consignment_id ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Shipment</p>
                        @if($log->shipment)
                            <a href="{{ route('admin.shipments.show', $log->shipment) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">{{ $log->shipment->shipment_number }}</a>
                        @else
                            <p class="text-sm text-gray-400">-</p>
                        @endif
                    </div>
                    <div><p class="text-xs font-medium text-gray-500">IP Address</p><p class="text-sm font-mono text-gray-900">{{ $log->ip_address ?? '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Idempotency Key</p><p class="text-sm font-mono text-gray-900 break-all">{{ $log->idempotency_key ?? '-' }}</p></div>
                    @if($log->processed_at)
                        <div><p class="text-xs font-medium text-gray-500">Processed At</p><p class="text-sm text-gray-900">{{ $log->processed_at->format('M d, Y h:i:s A') }}</p></div>
                    @endif
                </div>
            </div>

            @if($log->error_message)
                <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
                    <h3 class="text-lg font-semibold text-red-900 mb-2">Error</h3>
                    <p class="text-sm text-red-700 bg-red-50 rounded-lg p-3">{{ $log->error_message }}</p>
                </div>
            @endif

            @if($log->processing_result)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Processing Result</h3>
                    <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $log->processing_result }}</p>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payload</h3>
                <pre class="text-xs bg-gray-50 rounded-lg p-4 text-gray-700 overflow-x-auto max-h-96">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
            </div>

            @if($log->headers)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Headers (Safe)</h3>
                    <pre class="text-xs bg-gray-50 rounded-lg p-4 text-gray-700 overflow-x-auto max-h-64">{{ json_encode($log->headers, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @if(!$log->is_processed)
                        <form method="POST" action="{{ route('admin.courier-webhooks.retry', $log) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Retry Webhook</button>
                        </form>
                    @endif
                    @if($log->shipment)
                        <a href="{{ route('admin.shipments.show', $log->shipment) }}" class="block w-full text-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">View Shipment</a>
                    @endif
                    @if($log->courier)
                        <a href="{{ route('admin.couriers.show', $log->courier) }}" class="block w-full text-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">View Courier</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
