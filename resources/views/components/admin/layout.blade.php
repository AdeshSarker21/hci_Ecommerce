@props(['title' => 'Admin Panel', 'active' => ''])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-collapsed .nav-label { display: none; }
        .sidebar-collapsed .nav-section-label { display: none; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-gray-800">
    <div x-data="{ sidebarOpen: false, sidebarExpanded: true, profileOpen: false, notifOpen: false }" class="min-h-screen flex">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/40 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false">
        </div>

        {{-- Sidebar --}}
        <aside x-bind:class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full': !sidebarOpen,
                   'lg:translate-x-0': true,
                   'sidebar-collapsed': !sidebarExpanded
               }"
               x-bind:style="sidebarExpanded ? 'width: 260px' : 'width: 72px'"
               class="fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-200 transition-all duration-300 ease-in-out lg:static lg:z-auto flex flex-col overflow-hidden">

            {{-- Logo --}}
            <div class="flex items-center h-16 px-4 border-b border-gray-100 flex-shrink-0" x-bind:class="sidebarExpanded ? 'justify-between' : 'justify-center'">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition class="text-gray-900 font-bold text-lg whitespace-nowrap">{{ config('app.name') }}</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600 ml-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 admin-sidebar">
                <x-admin.nav-item icon="home" label="Dashboard" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="dashboard" />

                @if(auth()->user()->hasAnyPermission(['users.view', 'users.manage']))
                    <x-admin.nav-section label="Users" />
                    <x-admin.nav-item icon="users" label="All Users" href="{{ route('admin.users.index') }}" active="{{ $active }}" slug="users" />
                @endif

                @if(auth()->user()->hasAnyPermission(['roles.view', 'roles.manage', 'permissions.view']))
                    <x-admin.nav-section label="Access Control" />
                    <x-admin.nav-item icon="shield" label="Roles" href="{{ route('admin.roles.index') }}" active="{{ $active }}" slug="roles" />
                    <x-admin.nav-item icon="key" label="Permissions" href="{{ route('admin.permissions.index') }}" active="{{ $active }}" slug="permissions" />
                @endif

                @if(auth()->user()->hasAnyPermission(['products.view', 'products.manage']))
                    <x-admin.nav-section label="Catalog" />
                    <x-admin.nav-item icon="tag" label="Categories" href="{{ route('admin.categories.index') }}" active="{{ $active }}" slug="categories" />
                    <x-admin.nav-item icon="star" label="Brands" href="{{ route('admin.brands.index') }}" active="{{ $active }}" slug="brands" />
                    <x-admin.nav-item icon="cog" label="Attributes" href="{{ route('admin.attributes.index') }}" active="{{ $active }}" slug="attributes" />
                    <x-admin.nav-item icon="clipboard-list" label="Spec Fields" href="{{ route('admin.category-attributes.index') }}" active="{{ $active }}" slug="category-attributes" />
                    <x-admin.nav-item icon="cube" label="Products" href="{{ route('admin.products.index') }}" active="{{ $active }}" slug="products" />
                    <x-admin.nav-item icon="archive" label="Inventory" href="{{ route('admin.inventory.index') }}" active="{{ $active }}" slug="inventory" />
                    <x-admin.nav-item icon="building" label="Warehouses" href="{{ route('admin.warehouses.index') }}" active="{{ $active }}" slug="warehouses" />
                @endif

                @if(auth()->user()->hasAnyPermission(['orders.view', 'orders.manage']))
                    <x-admin.nav-section label="Sales" />
                    <x-admin.nav-item icon="shopping-cart" label="Orders" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="orders" />
                @endif

                @if(auth()->user()->hasAnyPermission(['vendors.view', 'vendors.manage']))
                    <x-admin.nav-section label="Marketplace" />
                    <x-admin.nav-item icon="store" label="Vendors" href="{{ route('admin.sellers.index') }}" active="{{ $active }}" slug="vendors" />
                @endif

                @if(auth()->user()->hasAnyPermission(['settings.view', 'settings.manage']))
                    <x-admin.nav-section label="Configuration" />
                    <x-admin.nav-item icon="cog" label="Settings" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="settings" />
                @endif
            </nav>

            {{-- Sidebar collapse toggle --}}
            <div class="hidden lg:flex items-center justify-center py-2 border-t border-gray-100 flex-shrink-0">
                <button @click="sidebarExpanded = !sidebarExpanded" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg x-bind:class="sidebarExpanded ? '' : 'rotate-180'" class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            {{-- User info --}}
            <div class="border-t border-gray-100 p-3 flex-shrink-0" x-bind:class="sidebarExpanded ? '' : 'flex justify-center'">
                <div class="flex items-center space-x-3" x-bind:class="sidebarExpanded ? '' : 'justify-center'">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full ring-2 ring-gray-100 flex-shrink-0">
                    <div x-show="sidebarExpanded" x-transition class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->roles->first()?->name ?? 'No Role' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top header --}}
            <header class="sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-md border-b border-gray-200 flex items-center justify-between px-4 lg:px-6">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700 p-1">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
                </div>
                <div class="flex items-center space-x-2">
                    {{-- Search --}}
                    <div class="hidden md:flex items-center bg-gray-50 rounded-lg px-3 py-2 border border-gray-200 focus-within:border-indigo-300 focus-within:ring-1 focus-within:ring-indigo-200 transition-all">
                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" placeholder="Search..." class="bg-transparent border-0 outline-none text-sm text-gray-700 placeholder-gray-400 w-40 focus:w-56 transition-all">
                    </div>

                    {{-- View Site --}}
                    <a href="/" target="_blank" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="View Site">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>

                    {{-- Notifications --}}
                    <div class="relative">
                        <button @click="notifOpen = !notifOpen; profileOpen = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors relative">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">Notifications</p>
                            </div>
                            <div class="px-4 py-6 text-center">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <p class="text-sm text-gray-500">No new notifications</p>
                            </div>
                        </div>
                    </div>

                    {{-- Profile dropdown --}}
                    <div class="relative">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" class="flex items-center space-x-2 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full ring-2 ring-gray-100">
                            <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                User Dashboard
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
