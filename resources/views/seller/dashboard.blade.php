<x-seller.layout title="Dashboard" active="dashboard">
    @if(session('success'))
        <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200/60 rounded-xl flex items-center gap-3">
            <div class="w-7 h-7 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-[13px] text-emerald-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Status Banners --}}
    @if($seller->isPending())
        <div class="mb-5 bg-amber-50 border border-amber-200/60 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[13px] font-semibold text-amber-800">Application Under Review</h3>
                    <p class="text-[12px] text-amber-700/80 mt-0.5">Your seller application is being reviewed. You'll be notified once approved.</p>
                </div>
            </div>
        </div>
    @elseif($seller->isRejected())
        <div class="mb-5 bg-red-50 border border-red-200/60 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l2-2m-2 2l-2-2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[13px] font-semibold text-red-800">Application Rejected</h3>
                    @if($seller->rejection_reason)
                        <p class="text-[12px] text-red-700/80 mt-0.5">{{ $seller->rejection_reason }}</p>
                    @endif
                </div>
            </div>
        </div>
    @elseif($seller->isSuspended())
        <div class="mb-5 bg-orange-50 border border-orange-200/60 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[13px] font-semibold text-orange-800">Store Suspended</h3>
                    @if($seller->rejection_reason)
                        <p class="text-[12px] text-orange-700/80 mt-0.5">{{ $seller->rejection_reason }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Welcome Header --}}
    <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
            <p class="text-[13px] text-slate-500 mt-0.5">Manage your store <span class="font-medium text-slate-700">{{ $seller->store_name }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge {{ $seller->status_badge }}">{{ ucfirst($seller->status) }}</span>
            @if($seller->is_featured)
                <span class="badge bg-violet-100 text-violet-700">Featured</span>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <a href="{{ route('seller.products.index') }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total Products</p>
            <p class="text-[18px] font-bold text-slate-900 mt-0.5">{{ $stats['total_products'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'published']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Published</p>
            <p class="text-[18px] font-bold text-emerald-600 mt-0.5">{{ $stats['published_products'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'pending_review']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Pending Review</p>
            <p class="text-[18px] font-bold text-amber-600 mt-0.5">{{ $stats['pending_products'] }}</p>
        </a>
        <a href="{{ route('seller.products.index', ['status' => 'draft']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-slate-50 rounded-lg flex items-center justify-center group-hover:bg-slate-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Drafts</p>
            <p class="text-[18px] font-bold text-slate-600 mt-0.5">{{ $stats['draft_products'] }}</p>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Recent Products --}}
        <div class="lg:col-span-2 content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Recent Products</h3>
                <a href="{{ route('seller.products.index') }}" class="text-[11.5px] text-emerald-600 hover:text-emerald-700 font-medium transition-colors">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentProducts as $product)
                    <div class="px-4 py-3 flex items-center gap-3 hover:bg-slate-50/50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                            @if($product->images->first())
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('seller.products.show', $product) }}" class="text-[12.5px] font-semibold text-slate-800 hover:text-emerald-600 truncate block">{{ $product->name }}</a>
                            <p class="text-[10.5px] text-slate-400 mt-0.5">${{ number_format($product->price, 2) }} @if($product->category) | {{ $product->category->name }} @endif</p>
                        </div>
                        <span class="badge {{ $product->status_badge }} flex-shrink-0">
                            {{ str_replace('_', ' ', ucfirst($product->status)) }}
                        </span>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <svg class="w-9 h-9 text-slate-200 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-[12.5px] text-slate-400">No products yet</p>
                        <a href="{{ route('seller.products.create') }}" class="mt-2 inline-flex items-center gap-1.5 text-[11.5px] text-emerald-600 hover:text-emerald-700 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Create Your First Product
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Store Info --}}
        <div class="space-y-4">
            <div class="content-card p-4">
                <h3 class="text-[14px] font-semibold text-slate-900 mb-3">Store Information</h3>
                <dl class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <dt class="text-[11.5px] text-slate-500">Commission Rate</dt>
                        <dd class="text-[12.5px] font-semibold text-slate-900">{{ $seller->commission_rate }}%</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[11.5px] text-slate-500">Staff Members</dt>
                        <dd class="text-[12.5px] font-semibold text-slate-900">{{ $stats['staff_count'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[11.5px] text-slate-500">Member Since</dt>
                        <dd class="text-[12.5px] font-semibold text-slate-900">{{ $seller->created_at->format('M Y') }}</dd>
                    </div>
                    @if($seller->contact_phone)
                        <div class="flex items-center justify-between">
                            <dt class="text-[11.5px] text-slate-500">Phone</dt>
                            <dd class="text-[12.5px] font-semibold text-slate-900">{{ $seller->contact_phone }}</dd>
                        </div>
                    @endif
                    @if($seller->full_address)
                        <div class="pt-2 border-t border-slate-100">
                            <dt class="text-[11.5px] text-slate-500 mb-0.5">Address</dt>
                            <dd class="text-[12px] text-slate-700 leading-snug">{{ $seller->full_address }}</dd>
                        </div>
                    @endif
                </dl>
                <div class="mt-3 pt-3 border-t border-slate-100 space-y-2">
                    <a href="{{ route('seller.profile.edit') }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 bg-white text-slate-700 text-[12px] font-medium rounded-lg border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Edit Store Settings
                    </a>
                    <a href="{{ route('seller.products.create') }}" class="w-full inline-flex items-center justify-center px-3.5 py-2 bg-emerald-600 text-white text-[12px] font-medium rounded-lg hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-500/20">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add New Product
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-seller.layout>
