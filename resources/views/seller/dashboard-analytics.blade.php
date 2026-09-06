<x-seller.layout title="Dashboard" active="dashboard">

    {{-- Success message --}}
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
            <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Welcome back, {{ $user->name ?? 'Seller' }}</h1>
            <p class="text-[13px] text-slate-500 mt-0.5">Here's what's happening with <span class="font-medium text-slate-700">{{ $seller->store_name }}</span> today.</p>
        </div>
        <div class="flex items-center gap-2">
            @if($storeUrl = $seller->isApproved() ? route('storefront.show', $seller->store_slug) : null)
                <a href="{{ $storeUrl }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-slate-700 text-[12.5px] font-medium rounded-lg border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Store
                </a>
            @endif
            <a href="{{ route('seller.products.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 text-white text-[12.5px] font-medium rounded-lg hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-500/20">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Product
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">
        {{-- Total Sales --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($stats['revenue_change'] > 0)
                    <span class="inline-flex items-center gap-0.5 text-[10.5px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full">
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $stats['revenue_change'] }}%
                    </span>
                @elseif($stats['revenue_change'] < 0)
                    <span class="inline-flex items-center gap-0.5 text-[10.5px] font-semibold text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full">
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ $stats['revenue_change'] }}%
                    </span>
                @endif
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total Sales</p>
            <p class="text-[18px] font-bold text-slate-900 mt-0.5">${{ number_format($stats['total_revenue'], 2) }}</p>
        </div>

        {{-- Total Orders --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                @if($stats['orders_change'] > 0)
                    <span class="inline-flex items-center gap-0.5 text-[10.5px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full">
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ $stats['orders_change'] }}%
                    </span>
                @elseif($stats['orders_change'] < 0)
                    <span class="inline-flex items-center gap-0.5 text-[10.5px] font-semibold text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full">
                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ $stats['orders_change'] }}%
                    </span>
                @endif
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total Orders</p>
            <p class="text-[18px] font-bold text-slate-900 mt-0.5">{{ number_format($stats['total_orders']) }}</p>
        </div>

        {{-- Total Products --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Products</p>
            <p class="text-[18px] font-bold text-slate-900 mt-0.5">{{ $stats['total_products'] }}</p>
            <p class="text-[10.5px] text-slate-400 mt-0.5">{{ $stats['published_products'] }} published</p>
        </div>

        {{-- Total Earnings --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center group-hover:bg-violet-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Total Earnings</p>
            <p class="text-[18px] font-bold text-slate-900 mt-0.5">${{ number_format($totalEarnings, 2) }}</p>
            <p class="text-[10.5px] text-slate-400 mt-0.5">${{ number_format($availableBalance, 2) }} available</p>
        </div>

        {{-- Pending Payout --}}
        <div class="stat-card group">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wide">Pending Payout</p>
            <p class="text-[18px] font-bold text-slate-900 mt-0.5">${{ number_format($pendingPayout, 2) }}</p>
            <p class="text-[10.5px] text-slate-400 mt-0.5">Next settlement</p>
        </div>
    </div>

    {{-- Period Filter --}}
    <div class="flex items-center gap-1.5 mb-5">
        @foreach(['7d' => '7D', '30d' => '30D', '90d' => '90D', '12m' => '1Y', 'all' => 'All'] as $value => $label)
            <a href="{{ route('seller.analytics', ['period' => $value]) }}"
               class="px-3 py-1.5 text-[12px] font-medium rounded-lg transition-all {{ $period === $value ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-500/20' : 'bg-white text-slate-500 border border-slate-200/80 hover:bg-slate-50 hover:text-slate-700 hover:border-slate-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Sales Chart + Performance --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        {{-- Sales Chart --}}
        <div class="lg:col-span-2 content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Sales Overview</h3>
                <div class="flex items-center gap-4 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        <span class="text-slate-500">Revenue</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        <span class="text-slate-500">Orders</span>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="relative" x-data="salesChart()" x-init="init()">
                    <canvas id="salesChart" class="w-full" style="height: 260px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Store Performance --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Store Performance</h3>
            </div>
            <div class="p-4 space-y-4">
                {{-- Rating --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11.5px] text-slate-500">Store Rating</span>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-[12px] font-semibold text-slate-900">{{ number_format($performance['average_rating'], 1) }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-amber-400 h-1.5 rounded-full transition-all duration-500" style="width: {{ ($performance['average_rating'] / 5) * 100 }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">{{ $performance['total_reviews'] }} reviews</p>
                </div>

                {{-- Response Rate --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11.5px] text-slate-500">Response Rate</span>
                        <span class="text-[11.5px] font-semibold text-slate-900">{{ $performance['response_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $performance['response_rate'] }}%"></div>
                    </div>
                </div>

                {{-- On-Time Delivery --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11.5px] text-slate-500">On-Time Delivery</span>
                        <span class="text-[11.5px] font-semibold text-slate-900">{{ $performance['on_time_delivery_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $performance['on_time_delivery_rate'] }}%"></div>
                    </div>
                </div>

                {{-- Avg Order Value --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11.5px] text-slate-500">Avg Order Value</span>
                        <span class="text-[11.5px] font-semibold text-slate-900">${{ number_format($stats['avg_order_value'], 2) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-purple-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $stats['avg_order_value'] * 10) }}%"></div>
                    </div>
                </div>

                {{-- Completed Orders --}}
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[11.5px] text-slate-500">Completed Orders</span>
                        <span class="text-[13px] font-bold text-slate-900">{{ number_format($stats['completed_orders']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Status Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <a href="{{ route('seller.orders.index', ['status' => 'pending']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[10.5px] font-medium text-slate-400 uppercase tracking-wide">Pending</p>
            <p class="text-[18px] font-bold text-amber-600 mt-0.5">{{ $stats['pending_orders'] }}</p>
        </a>
        <a href="{{ route('seller.orders.index', ['status' => 'processing']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
            </div>
            <p class="text-[10.5px] font-medium text-slate-400 uppercase tracking-wide">Processing</p>
            <p class="text-[18px] font-bold text-blue-600 mt-0.5">{{ $stats['total_orders'] - $stats['pending_orders'] - $stats['completed_orders'] - $stats['cancelled_orders'] }}</p>
        </a>
        <a href="{{ route('seller.orders.index', ['status' => 'delivered']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-[10.5px] font-medium text-slate-400 uppercase tracking-wide">Completed</p>
            <p class="text-[18px] font-bold text-emerald-600 mt-0.5">{{ $stats['completed_orders'] }}</p>
        </a>
        <a href="{{ route('seller.orders.index', ['status' => 'cancelled']) }}" class="stat-card group text-left">
            <div class="flex items-center justify-between mb-2">
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition-colors">
                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <p class="text-[10.5px] font-medium text-slate-400 uppercase tracking-wide">Cancelled</p>
            <p class="text-[18px] font-bold text-red-500 mt-0.5">{{ $stats['cancelled_orders'] }}</p>
        </a>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        {{-- Recent Orders --}}
        <div class="lg:col-span-2 content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Recent Orders</h3>
                <a href="{{ route('seller.orders.index') }}" class="text-[11.5px] text-emerald-600 hover:text-emerald-700 font-medium transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-4 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider">Order</th>
                            <th class="px-4 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2.5 text-left text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('seller.orders.show', $order) }}" class="text-[12.5px] font-semibold text-emerald-600 hover:text-emerald-700">
                                        #{{ $order->order_number }}
                                    </a>
                                    <p class="text-[10.5px] text-slate-400">{{ $order->items->count() }} item(s)</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="text-[12.5px] font-medium text-slate-800">{{ $order->customer_name }}</p>
                                    <p class="text-[10.5px] text-slate-400">{{ $order->user?->email }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-[12.5px] font-semibold text-slate-900">${{ $order->formatted_total }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="badge {{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-[11.5px] text-slate-500">
                                    {{ $order->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center">
                                    <svg class="w-9 h-9 text-slate-200 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-[12.5px] text-slate-400">No orders yet</p>
                                    <p class="text-[10.5px] text-slate-300 mt-0.5">Orders will appear here once customers start purchasing</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Selling Products --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Top Products</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topProducts as $product)
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
                            <p class="text-[12px] font-semibold text-slate-800 truncate">{{ $product->name }}</p>
                            <p class="text-[10.5px] text-slate-400">{{ $product->total_orders }} sales</p>
                        </div>
                        <span class="text-[12px] font-bold text-slate-900">${{ number_format($product->total_revenue ?? 0, 2) }}</span>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center">
                        <svg class="w-7 h-7 text-slate-200 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="text-[12px] text-slate-400">No sales data yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Low Stock Alerts --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Low Stock Alerts</h3>
                @if($stats['low_stock_count'] > 0)
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700">{{ $stats['low_stock_count'] }}</span>
                @endif
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($lowStockProducts as $product)
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
                            <p class="text-[12px] font-semibold text-slate-800 truncate">{{ $product->name }}</p>
                            <p class="text-[10.5px] text-slate-400">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[12px] font-bold text-red-600">{{ $product->quantity }}</p>
                            <p class="text-[9.5px] text-slate-400">left</p>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center">
                        <svg class="w-7 h-7 text-emerald-200 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[12px] text-slate-400">All stock levels healthy</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Products --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Pending Approval</h3>
                @if($stats['pending_products'] > 0)
                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">{{ $stats['pending_products'] }}</span>
                @endif
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($pendingProducts as $product)
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
                            <p class="text-[12px] font-semibold text-slate-800 truncate">{{ $product->name }}</p>
                            <p class="text-[10.5px] text-slate-400">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                        </div>
                        <a href="{{ route('seller.products.show', $product) }}" class="text-[10.5px] text-emerald-600 hover:text-emerald-700 font-medium">View</a>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center">
                        <svg class="w-7 h-7 text-emerald-200 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[12px] text-slate-400">No pending products</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="text-[14px] font-semibold text-slate-900">Recent Activity</h3>
            </div>
            <div class="divide-y divide-slate-50 max-h-[360px] overflow-y-auto">
                @forelse($recentActivity as $activity)
                    <div class="px-4 py-2.5 flex items-start gap-2.5">
                        <div class="w-7 h-7 rounded-md flex items-center justify-center flex-shrink-0 mt-0.5
                            {{ match($activity['color']) {
                                'yellow' => 'bg-amber-50',
                                'blue' => 'bg-blue-50',
                                'indigo' => 'bg-indigo-50',
                                'green' => 'bg-emerald-50',
                                'red' => 'bg-red-50',
                                'gray' => 'bg-slate-50',
                                default => 'bg-slate-50',
                            } }}">
                            @if($activity['type'] === 'order')
                                <svg class="w-3.5 h-3.5 {{ match($activity['color']) {
                                    'yellow' => 'text-amber-600',
                                    'blue' => 'text-blue-600',
                                    'indigo' => 'text-indigo-600',
                                    'green' => 'text-emerald-600',
                                    'red' => 'text-red-600',
                                    'gray' => 'text-slate-600',
                                    default => 'text-slate-600',
                                } }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            @else
                                <svg class="w-3.5 h-3.5 {{ match($activity['color']) {
                                    'yellow' => 'text-amber-600',
                                    'blue' => 'text-blue-600',
                                    'green' => 'text-emerald-600',
                                    'red' => 'text-red-600',
                                    'gray' => 'text-slate-600',
                                    default => 'text-slate-600',
                                } }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11.5px] text-slate-700 leading-snug">{{ $activity['message'] }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[10px] text-slate-400">{{ $activity['time'] }}</span>
                                <span class="text-[10px] text-slate-300">&middot;</span>
                                <span class="text-[10px] text-slate-500 font-medium">{{ $activity['detail'] }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center">
                        <p class="text-[12px] text-slate-400">No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function salesChart() {
            return {
                init() {
                    const ctx = document.getElementById('salesChart').getContext('2d');
                    const data = @json($salesData);

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.map(d => {
                                const date = new Date(d.date);
                                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                            }),
                            datasets: [
                                {
                                    label: 'Revenue',
                                    data: data.map(d => d.revenue),
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.06)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 2,
                                    pointRadius: 0,
                                    pointHoverRadius: 4,
                                    pointHoverBackgroundColor: '#10b981',
                                    pointHoverBorderColor: '#fff',
                                    pointHoverBorderWidth: 2,
                                    yAxisID: 'y',
                                },
                                {
                                    label: 'Orders',
                                    data: data.map(d => d.order_count),
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.03)',
                                    fill: false,
                                    tension: 0.4,
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    pointRadius: 0,
                                    pointHoverRadius: 4,
                                    pointHoverBackgroundColor: '#3b82f6',
                                    pointHoverBorderColor: '#fff',
                                    pointHoverBorderWidth: 2,
                                    yAxisID: 'y1',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    titleFont: { size: 11, weight: '600', family: 'Inter' },
                                    bodyFont: { size: 11, family: 'Inter' },
                                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                                    cornerRadius: 8,
                                    displayColors: true,
                                    boxWidth: 8,
                                    boxHeight: 8,
                                    boxPadding: 4,
                                    usePointStyle: true,
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { maxTicksLimit: 8, font: { size: 10.5, family: 'Inter' }, color: '#94a3b8' },
                                    border: { display: false },
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                                    ticks: { callback: v => '$' + v, font: { size: 10.5, family: 'Inter' }, color: '#94a3b8', padding: 8 },
                                    border: { display: false },
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: { font: { size: 10.5, family: 'Inter' }, color: '#94a3b8', padding: 8 },
                                    border: { display: false },
                                },
                            },
                        },
                    });
                }
            };
        }
    </script>
    @endpush
</x-seller.layout>
