<x-admin.layout title="{{ $courier->name }}" active="couriers">
    <div class="mb-6">
        <a href="{{ route('admin.couriers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Couriers
        </a>
        <div class="flex justify-between items-start">
            <div class="flex items-center gap-4">
                @if($courier->logo)
                    <img src="{{ $courier->logo }}" alt="{{ $courier->name }}" class="w-16 h-16 rounded-xl object-contain bg-gray-50 border">
                @else
                    <div class="w-16 h-16 bg-indigo-50 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $courier->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Code: {{ $courier->code }} &middot; Slug: {{ $courier->slug }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.couriers.toggle-status', $courier) }}" class="inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $courier->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $courier->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </form>
                @if(!$courier->is_default)
                    <form method="POST" action="{{ route('admin.couriers.set-default', $courier) }}" class="inline">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200">Set as Default</button>
                    </form>
                @endif
                @if($courier->api_key)
                    <form method="POST" action="{{ route('admin.couriers.test-connection', $courier) }}" class="inline">
                        @csrf @method('POST')
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200">Test API</button>
                    </form>
                @endif
                <a href="{{ route('admin.couriers.edit', $courier) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200">Edit</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><p class="text-xs font-medium text-gray-500">Name</p><p class="text-sm font-semibold text-gray-900">{{ $courier->name }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Code</p><p class="text-sm font-semibold text-gray-900 font-mono">{{ $courier->code }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Status</p><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $courier->status_badge }}">{{ $courier->status_label }}</span></div>
                    <div><p class="text-xs font-medium text-gray-500">Default</p><p class="text-sm text-gray-900">{{ $courier->is_default ? 'Yes' : 'No' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Website</p>
                        @if($courier->website)
                            <a href="{{ $courier->website }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-700">{{ $courier->website }}</a>
                        @else
                            <p class="text-sm text-gray-400">-</p>
                        @endif
                    </div>
                    <div><p class="text-xs font-medium text-gray-500">Webhook URL</p><p class="text-sm font-mono text-gray-900 break-all">{{ $courier->webhook_url }}</p></div>
                    @if($courier->description)
                        <div class="sm:col-span-2"><p class="text-xs font-medium text-gray-500">Description</p><p class="text-sm text-gray-700">{{ $courier->description }}</p></div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">API Configuration</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">API Base URL</span><span class="text-sm font-mono text-gray-900">{{ $courier->api_base_url ?? 'Not configured' }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">API Key</span><span class="text-sm text-gray-900">{{ $courier->api_key ? '••••••••' . substr($courier->api_key_decrypted, -4) : 'Not set' }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">API Secret</span><span class="text-sm text-gray-900">{{ $courier->api_secret ? '••••••••' : 'Not set' }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Webhook Secret</span><span class="text-sm text-gray-900">{{ $courier->webhook_secret ? '••••••••' : 'Not set' }}</span></div>
                    @if($courier->token_expires_at)
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Token Expires</span><span class="text-sm text-gray-900">{{ $courier->token_expires_at->format('M d, Y h:i A') }} {{ $courier->token_expires_at->isPast() ? '(Expired)' : '' }}</span></div>
                    @endif
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Last Synced</span><span class="text-sm text-gray-900">{{ $courier->last_synced_at?->diffForHumans() ?? 'Never' }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Supported Services</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="flex items-center gap-2 p-3 rounded-lg {{ $courier->cod_support ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="w-3 h-3 rounded-full {{ $courier->cod_support ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        <span class="text-sm {{ $courier->cod_support ? 'text-green-700 font-medium' : 'text-gray-500' }}">COD</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 rounded-lg {{ $courier->tracking_support ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="w-3 h-3 rounded-full {{ $courier->tracking_support ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        <span class="text-sm {{ $courier->tracking_support ? 'text-green-700 font-medium' : 'text-gray-500' }}">Tracking</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 rounded-lg {{ $courier->shipment_creation_support ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="w-3 h-3 rounded-full {{ $courier->shipment_creation_support ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        <span class="text-sm {{ $courier->shipment_creation_support ? 'text-green-700 font-medium' : 'text-gray-500' }}">Create Shipment</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 rounded-lg {{ $courier->return_support ? 'bg-green-50' : 'bg-gray-50' }}">
                        <div class="w-3 h-3 rounded-full {{ $courier->return_support ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        <span class="text-sm {{ $courier->return_support ? 'text-green-700 font-medium' : 'text-gray-500' }}">Returns</span>
                    </div>
                </div>
            </div>

            @if($recentShipments->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Shipments</h3>
                        <a href="{{ route('admin.shipments.index', ['courier_id' => $courier->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-700">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Shipment #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentShipments as $shipment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm font-medium text-indigo-600">{{ $shipment->shipment_number }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-600">#{{ $shipment->order?->order_number ?? 'N/A' }}</td>
                                        <td class="px-4 py-2"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $shipment->status_badge }}">{{ $shipment->status_label }}</span></td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $shipment->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($recentWebhooks->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Webhooks</h3>
                        <a href="{{ route('admin.courier-webhooks.index', ['courier_id' => $courier->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-700">View All</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($recentWebhooks as $log)
                            <a href="{{ route('admin.courier-webhooks.show', $log) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-3">
                                    @if($log->is_processed)
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    @elseif($log->error_message)
                                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    @else
                                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $log->event_type ?? 'Unknown' }}</p>
                                        <p class="text-xs text-gray-500">{{ $log->consignment_id ?? '-' }} &middot; {{ $log->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Total Shipments</span><span class="text-lg font-bold text-gray-900">{{ $stats['total_shipments'] }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Active</span><span class="text-lg font-bold text-blue-600">{{ $stats['active_shipments'] }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Delivered</span><span class="text-lg font-bold text-green-600">{{ $stats['delivered'] }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Returned</span><span class="text-lg font-bold text-red-600">{{ $stats['returned'] }}</span></div>
                    <div class="border-t border-gray-100 pt-3 mt-3">
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Total Webhooks</span><span class="text-lg font-bold text-gray-900">{{ $stats['total_webhooks'] }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-sm text-gray-500">Failed Webhooks</span><span class="text-lg font-bold text-red-600">{{ $stats['failed_webhooks'] }}</span></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    <form method="POST" action="{{ route('admin.couriers.test-connection', $courier) }}">
                        @csrf @method('POST')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">Test API Connection</button>
                    </form>
                    <a href="{{ route('admin.couriers.api-settings') }}" class="block w-full text-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">API Settings</a>
                    <a href="{{ route('admin.couriers.edit', $courier) }}" class="block w-full text-center px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">Edit Courier</a>
                    <form method="POST" action="{{ route('admin.couriers.destroy', $courier) }}" onsubmit="return confirm('Are you sure you want to delete this courier?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">Delete Courier</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
