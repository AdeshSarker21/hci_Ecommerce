<x-seller.layout title="Dashboard" active="dashboard">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if($seller->isPending())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-yellow-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800">Application Under Review</h3>
                    <p class="text-sm text-yellow-700 mt-1">Your seller application is being reviewed by our team. You will be notified once it's approved.</p>
                </div>
            </div>
        </div>
    @elseif($seller->isRejected())
        <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-red-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l2-2m-2 2l-2-2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-red-800">Application Rejected</h3>
                    @if($seller->rejection_reason)
                        <p class="text-sm text-red-700 mt-1">{{ $seller->rejection_reason }}</p>
                    @endif
                </div>
            </div>
        </div>
    @elseif($seller->isSuspended())
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 mb-6">
            <div class="flex items-center">
                <svg class="w-10 h-10 text-orange-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-orange-800">Store Suspended</h3>
                    @if($seller->rejection_reason)
                        <p class="text-sm text-orange-700 mt-1">{{ $seller->rejection_reason }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <div class="flex items-center space-x-4">
            <img src="{{ $seller->logo_url }}" alt="{{ $seller->store_name }}" class="w-16 h-16 rounded-xl">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $seller->store_name }}</h2>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $seller->status_badge }}">{{ ucfirst($seller->status) }}</span>
                    @if($seller->is_featured)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Featured</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Commission Rate</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $seller->commission_rate }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Staff Members</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['staff_count'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Store Status</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ ucfirst($seller->status) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Member Since</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $seller->created_at->format('M Y') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Store Information</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Contact Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $seller->contact_email }}</dd>
            </div>
            @if($seller->contact_phone)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $seller->contact_phone }}</dd>
                </div>
            @endif
            @if($seller->contact_website)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Website</dt>
                    <dd class="mt-1 text-sm text-gray-900"><a href="{{ $seller->contact_website }}" target="_blank" class="text-indigo-600 hover:underline">{{ $seller->contact_website }}</a></dd>
                </div>
            @endif
            @if($seller->full_address)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $seller->full_address }}</dd>
                </div>
            @endif
        </dl>
    </div>
</x-seller.layout>
