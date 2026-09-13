<x-admin.layout title="API Settings" active="courier-api">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Courier API Settings</h2>
        <p class="text-sm text-gray-500 mt-1">Configure API credentials for all courier providers</p>
    </div>

    @if(session('success'))
        <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <div class="space-y-6">
        @forelse($couriers as $courier)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        @if($courier->logo)
                            <img src="{{ $courier->logo }}" alt="{{ $courier->name }}" class="w-10 h-10 rounded-lg object-contain bg-gray-50">
                        @else
                            <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $courier->name }}</h3>
                            <p class="text-xs text-gray-500">{{ $courier->slug }} &middot; {{ $courier->is_active ? 'Active' : 'Inactive' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @php $connStatus = $courier->api_connection_status; @endphp
                        @if($connStatus === 'connected')
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Connected</span>
                        @elseif($connStatus === 'configured')
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Configured</span>
                        @elseif($connStatus === 'token_expired')
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Token Expired</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Not Configured</span>
                        @endif
                        <form method="POST" action="{{ route('admin.couriers.test-connection', $courier) }}" class="inline">
                            @csrf @method('POST')
                            <button type="submit" class="px-3 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200">Test</button>
                        </form>
                    </div>
                </div>

                <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 mb-1">Webhook URL</p>
                    <p class="text-sm font-mono text-gray-900 break-all">{{ $courier->webhook_url }}</p>
                </div>

                <form method="POST" action="{{ route('admin.couriers.api-settings.update', $courier) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Base URL</label>
                            <input type="url" name="api_base_url" value="{{ $courier->api_base_url }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="https://api.example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                            <input type="password" name="webhook_secret" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="{{ $courier->webhook_secret ? '•••••••• (set)' : 'Not set' }}">
                            <p class="text-xs text-gray-400 mt-1">Leave blank to keep current</p>
                        </div>

                        @if($courier->slug === 'pathao')
                            <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                                <p class="text-sm font-medium text-gray-700 mb-3">Pathao OAuth Credentials</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                                <input type="text" name="config[client_id]" value="{{ data_get($courier->config, 'client_id', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Pathao Client ID">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                                <input type="password" name="config[client_secret]" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="{{ data_get($courier->config, 'client_secret') ? '•••••••• (set)' : 'Not set' }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" name="config[username]" value="{{ data_get($courier->config, 'username', '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="Merchant username">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" name="config[password]" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="{{ data_get($courier->config, 'password') ? '•••••••• (set)' : 'Not set' }}">
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                                <input type="password" name="api_key" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="{{ $courier->api_key ? '•••••••• (set)' : 'Not set' }}">
                                <p class="text-xs text-gray-400 mt-1">Leave blank to keep current</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
                                <input type="password" name="api_secret" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400" placeholder="{{ $courier->api_secret ? '•••••••• (set)' : 'Not set' }}">
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">Save Settings</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-500 text-sm">No couriers configured yet.</p>
                <a href="{{ route('admin.couriers.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium">Add a courier</a>
            </div>
        @endforelse
    </div>
</x-admin.layout>
