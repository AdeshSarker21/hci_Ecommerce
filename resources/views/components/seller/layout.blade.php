@props(['title' => 'Seller Dashboard', 'active' => ''])

@php
    $user = auth()->user();
    $seller = $user->seller;
    $isApproved = $seller && $seller->isApproved();
    $isOwner = $seller && $seller->user_id === $user->id;

    $staff = $isOwner ? null : \App\Models\SellerStaff::where('seller_id', $seller?->id)->where('user_id', $user->id)->first();
    $isStaff = $staff && $staff->isActive();

    $canProducts = $isOwner || ($isStaff && $staff->can_manage_products);
    $canOrders = $isOwner || ($isStaff && $staff->can_manage_orders);
    $canSettings = $isOwner || ($isStaff && $staff->can_manage_settings);
    $canReports = $isOwner || ($isStaff && $staff->can_view_reports);

    $isActive = fn ($route, $exact = false) => $exact ? request()->routeIs($route) : request()->routeIs($route . '*');

    $productsOpen = $isActive('seller.products') || $isActive('seller.inventory');
    $financeOpen = $isActive('seller.commission') || $isActive('seller.wallet') || $isActive('seller.withdrawals') || $isActive('seller.earnings');
    $analyticsOpen = $isActive('seller.analytics');

    $storeUrl = $isApproved ? route('storefront.show', $seller->store_slug) : null;

    $pendingOrdersCount = $isApproved ? \App\Models\Order::where('seller_id', $seller->id)->where('status', 'pending')->count() : 0;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .seller-sidebar { scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }
        .seller-sidebar::-webkit-scrollbar { width: 4px; }
        .seller-sidebar::-webkit-scrollbar-track { background: transparent; }
        .seller-sidebar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 4px; }
        .seller-sidebar::-webkit-scrollbar-thumb:hover { background-color: #cbd5e1; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #475569;
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .nav-item:hover { background: #f1f5f9; color: #0f172a; }
        .nav-item.active { background: #ecfdf5; color: #059669; font-weight: 600; }
        .nav-sub-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem 0.375rem 2.25rem;
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            color: #64748b;
            transition: all 0.15s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .nav-sub-item:hover { background: #f8fafc; color: #334155; }
        .nav-sub-item.active { background: #f0fdf4; color: #059669; font-weight: 500; }
        .nav-label {
            font-size: 0.6875rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 0.75rem 0.375rem;
        }
        .header-search:focus { box-shadow: 0 0 0 3px rgba(16,185,129,0.12); border-color: #10b981; }
        .stat-card {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 0.875rem;
            padding: 1.25rem;
            transition: all 0.2s ease;
        }
        .stat-card:hover { border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
        .content-card {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 0.875rem;
            overflow: hidden;
        }
        .content-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
            line-height: 1.25rem;
        }
    </style>
</head>
<body class="h-full bg-[#f8fafc] antialiased">
    <div x-data="{ sidebarOpen: false }" class="h-full flex">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false">
        </div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="seller-sidebar fixed inset-y-0 left-0 z-50 w-[264px] bg-white border-r border-slate-100 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto flex flex-col">

            {{-- Logo --}}
            <div class="flex items-center h-14 px-4 border-b border-slate-100/80">
                <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-sm shadow-emerald-500/20 group-hover:shadow-md group-hover:shadow-emerald-500/30 transition-shadow">
                        <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[14px] font-bold text-slate-900 tracking-tight">Seller Center</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="ml-auto lg:hidden text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100 transition-colors">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="seller-sidebar flex-1 overflow-y-auto py-3 px-3 space-y-0.5"
                 x-data="{ openProducts: {{ $productsOpen ? 'true' : 'false' }}, openFinance: {{ $financeOpen ? 'true' : 'false' }}, openAnalytics: {{ $analyticsOpen ? 'true' : 'false' }} }">

                {{-- Dashboard --}}
                <a href="{{ $isApproved ? route('seller.analytics') : route('seller.dashboard') }}"
                   class="nav-item {{ ($isActive('seller.analytics', true) || $isActive('seller.dashboard', true)) ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                @if($isApproved)

                    {{-- My Store --}}
                    @if($canSettings)
                        <a href="{{ route('seller.profile.edit') }}"
                           class="nav-item {{ $isActive('seller.profile.edit', true) ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            My Store
                        </a>
                        <a href="{{ route('seller.profile.edit') }}"
                           class="nav-item">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Store Settings
                        </a>
                    @endif

                    {{-- Products --}}
                    @if($canProducts)
                        <div class="nav-label">Products</div>
                        <button @click="openProducts = !openProducts"
                                class="nav-item w-full {{ ($isActive('seller.products') || $isActive('seller.inventory')) ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Products
                            <svg class="w-3.5 h-3.5 ml-auto transition-transform duration-200 {{ $productsOpen ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openProducts" x-collapse x-cloak class="space-y-0.5">
                            <a href="{{ route('seller.products.index') }}"
                               class="nav-sub-item {{ $isActive('seller.products.index', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                All Products
                            </a>
                            <a href="{{ route('seller.products.create') }}"
                               class="nav-sub-item {{ $isActive('seller.products.create', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Add Product
                            </a>
                        </div>

                        <a href="{{ route('seller.inventory.index') }}"
                           class="nav-item {{ $isActive('seller.inventory', true) ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            Inventory
                        </a>
                    @endif

                    {{-- Reviews --}}
                    @if($isOwner)
                        <a href="{{ route('seller.reviews.index') }}"
                           class="nav-item {{ $isActive('seller.reviews', true) ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            Reviews
                        </a>
                    @endif

                    {{-- Orders --}}
                    @if($canOrders)
                        <div class="nav-label">Orders</div>
                        <a href="{{ route('seller.orders.index') }}"
                           class="nav-item {{ $isActive('seller.orders', true) ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            Orders
                            @if($pendingOrdersCount > 0)
                                <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full">{{ $pendingOrdersCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('seller.shipments.index') }}"
                           class="nav-item {{ $isActive('seller.shipments', true) ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Shipments
                        </a>
                    @endif

                    {{-- Finance --}}
                    @if($isOwner)
                        <div class="nav-label">Finance</div>
                        <button @click="openFinance = !openFinance"
                                class="nav-item w-full {{ $financeOpen ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Finance
                            <svg class="w-3.5 h-3.5 ml-auto transition-transform duration-200 {{ $financeOpen ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openFinance" x-collapse x-cloak class="space-y-0.5">
                            <a href="{{ route('seller.commission.index') }}"
                               class="nav-sub-item {{ $isActive('seller.commission.index', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Commission
                            </a>
                            <a href="{{ route('seller.commission.earnings') }}"
                               class="nav-sub-item {{ $isActive('seller.commission.earnings', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Earnings
                            </a>
                            <a href="{{ route('seller.wallet.index') }}"
                               class="nav-sub-item {{ $isActive('seller.wallet.index', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Wallet
                            </a>
                            <a href="{{ route('seller.withdrawals.index') }}"
                               class="nav-sub-item {{ $isActive('seller.withdrawals.index', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Withdrawals
                            </a>
                            <a href="{{ route('seller.withdrawals.index') }}"
                               class="nav-sub-item {{ $isActive('seller.settlements', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Settlements
                            </a>
                        </div>
                    @endif

                    {{-- Analytics --}}
                    @if($canReports)
                        <div class="nav-label">Analytics</div>
                        <button @click="openAnalytics = !openAnalytics"
                                class="nav-item w-full {{ $analyticsOpen ? 'active' : '' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Analytics
                            <svg class="w-3.5 h-3.5 ml-auto transition-transform duration-200 {{ $analyticsOpen ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div x-show="openAnalytics" x-collapse x-cloak class="space-y-0.5">
                            <a href="{{ route('seller.analytics') }}"
                               class="nav-sub-item {{ $isActive('seller.analytics', true) ? 'active' : '' }}">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0 opacity-50"></span>
                                Sales Analytics
                            </a>
                        </div>
                    @endif

                    {{-- Notifications --}}
                    <div class="nav-label">System</div>
                    <a href="{{ route('seller.notifications.index') }}"
                       class="nav-item {{ $isActive('seller.notifications', true) ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notifications
                    </a>

                    {{-- Profile --}}
                    <a href="{{ route('seller.profile.edit') }}"
                       class="nav-item {{ $isActive('seller.profile', true) ? 'active' : '' }}">
                        <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile
                    </a>

                @endif
            </nav>

            {{-- Sidebar footer --}}
            <div class="border-t border-slate-100/80 p-3">
                <div class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-default">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full ring-2 ring-slate-100 object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-[12.5px] font-semibold text-slate-800 truncate leading-tight">{{ $user->name }}</p>
                        <p class="text-[10.5px] text-slate-400 truncate leading-tight">{{ $seller?->store_name ?? 'No Store' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main wrapper --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Top Header --}}
            <header class="sticky top-0 z-30 h-14 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-4 lg:px-5">
                {{-- Left: toggle + search --}}
                <div class="flex items-center gap-3 flex-1">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="hidden sm:flex items-center flex-1 max-w-sm">
                        <div class="relative w-full">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" placeholder="Search products, orders..."
                                   class="header-search w-full pl-10 pr-4 py-[0.4375rem] text-[13px] bg-slate-50/80 border border-slate-200/80 rounded-lg focus:outline-none focus:bg-white focus:border-emerald-300 transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                </div>

                {{-- Right: actions --}}
                <div class="flex items-center gap-1.5">
                    {{-- View Store --}}
                    @if($storeUrl)
                        <a href="{{ $storeUrl }}" target="_blank"
                           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-[0.375rem] text-[12.5px] font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 hover:border-slate-300 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            View Store
                        </a>
                    @endif

                    {{-- Notifications --}}
                    <button class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>

                    {{-- Store Status --}}
                    @if($seller)
                        <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-full border {{ $isApproved ? 'bg-emerald-50 border-emerald-200/60' : ($seller->isPending() ? 'bg-amber-50 border-amber-200/60' : 'bg-red-50 border-red-200/60') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isApproved ? 'bg-emerald-500' : ($seller->isPending() ? 'bg-amber-500' : 'bg-red-500') }} animate-pulse"></span>
                            <span class="text-[11px] font-semibold {{ $isApproved ? 'text-emerald-700' : ($seller->isPending() ? 'text-amber-700' : 'text-red-700') }}">
                                {{ ucfirst($seller->status) }}
                            </span>
                        </div>
                    @endif

                    {{-- Profile dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full ring-2 ring-slate-100 object-cover">
                            <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg shadow-slate-200/60 border border-slate-100 py-1.5 z-50">
                            <div class="px-3.5 py-2.5 border-b border-slate-100">
                                <p class="text-[13px] font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $user->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[12.5px] text-slate-600 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                    User Dashboard
                                </a>
                                <a href="{{ route('seller.profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[12.5px] text-slate-600 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Edit Profile
                                </a>
                            </div>
                            <div class="border-t border-slate-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2.5 w-full px-3.5 py-2 text-[12.5px] text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-5 overflow-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
