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
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div x-data="{ sidebarOpen: false, sidebarExpanded: true }" class="min-h-screen flex">

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto flex flex-col">

            {{-- Logo --}}
            <div class="flex items-center justify-between h-16 px-4 bg-gray-900 border-b border-gray-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-lg">{{ config('app.name') }}</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <x-admin.nav-item icon="home" label="Dashboard" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="dashboard" />

                {{-- Users Section --}}
                @if(auth()->user()->hasAnyPermission(['users.view', 'users.manage']))
                    <x-admin.nav-section label="Users" />
                    <x-admin.nav-item icon="users" label="All Users" href="{{ route('admin.users.index') }}" active="{{ $active }}" slug="users" />
                @endif

                {{-- Roles & Permissions --}}
                @if(auth()->user()->hasAnyPermission(['roles.view', 'roles.manage', 'permissions.view']))
                    <x-admin.nav-section label="Access Control" />
                    <x-admin.nav-item icon="shield" label="Roles" href="{{ route('admin.roles.index') }}" active="{{ $active }}" slug="roles" />
                    <x-admin.nav-item icon="key" label="Permissions" href="{{ route('admin.permissions.index') }}" active="{{ $active }}" slug="permissions" />
                @endif

                {{-- Products (placeholder) --}}
                @if(auth()->user()->hasAnyPermission(['products.view', 'products.manage']))
                    <x-admin.nav-section label="Catalog" />
                    <x-admin.nav-item icon="tag" label="Categories" href="{{ route('admin.categories.index') }}" active="{{ $active }}" slug="categories" />
                    <x-admin.nav-item icon="star" label="Brands" href="{{ route('admin.brands.index') }}" active="{{ $active }}" slug="brands" />
                    <x-admin.nav-item icon="cog" label="Attributes" href="{{ route('admin.attributes.index') }}" active="{{ $active }}" slug="attributes" />
                    <x-admin.nav-item icon="cube" label="Products" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="products" />
                @endif

                {{-- Orders (placeholder) --}}
                @if(auth()->user()->hasAnyPermission(['orders.view', 'orders.manage']))
                    <x-admin.nav-section label="Sales" />
                    <x-admin.nav-item icon="shopping-cart" label="Orders" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="orders" />
                @endif

                {{-- Vendors --}}
                @if(auth()->user()->hasAnyPermission(['vendors.view', 'vendors.manage']))
                    <x-admin.nav-section label="Marketplace" />
                    <x-admin.nav-item icon="store" label="Vendors" href="{{ route('admin.sellers.index') }}" active="{{ $active }}" slug="vendors" />
                @endif

                {{-- Settings --}}
                @if(auth()->user()->hasAnyPermission(['settings.view', 'settings.manage']))
                    <x-admin.nav-section label="Configuration" />
                    <x-admin.nav-item icon="cog" label="Settings" href="{{ route('admin.dashboard') }}" active="{{ $active }}" slug="settings" />
                @endif
            </nav>

            {{-- User info --}}
            <div class="border-t border-gray-800 p-4">
                <div class="flex items-center space-x-3">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-full">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->roles->first()?->name ?? 'No Role' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top header --}}
            <header class="sticky top-0 z-30 h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" target="_blank" class="text-gray-400 hover:text-gray-600" title="View Site">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-gray-900">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full">
                            <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">User Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
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
