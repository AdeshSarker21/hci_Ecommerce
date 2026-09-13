@props(['title' => 'My Account', 'active' => 'dashboard'])

@php
    $user = auth()->user();
    $isActive = fn ($route) => request()->routeIs($route) ? 'active' : '';
    $cartCount = \App\Models\CartItem::where('user_id', $user->id)->sum('quantity');
    $wishlistCount = \App\Models\Wishlist::where('user_id', $user->id)->count();
    $pendingOrders = \App\Models\Order::where('user_id', $user->id)->where('status', 'pending')->count();
@endphp

<x-storefront.layout :title="$title">
    <div class="min-h-screen bg-[#fafafa]" x-data="{ sidebarOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 lg:py-10">

            {{-- Mobile Header --}}
            <div class="lg:hidden flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full ring-2 ring-emerald-100 object-cover">
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>
                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <div class="flex gap-6">

                {{-- Sidebar --}}
                <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                       class="fixed inset-y-0 left-0 z-50 w-[280px] bg-white border-r border-gray-100 transition-transform duration-300 lg:static lg:z-auto lg:w-[260px] flex-shrink-0 lg:block"
                       @click.away="sidebarOpen = false">

                    {{-- Mobile Overlay --}}
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

                    <div class="h-full overflow-y-auto p-4">
                        {{-- User Info --}}
                        <div class="flex items-center gap-3 p-4 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl mb-6">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-12 h-12 rounded-full ring-2 ring-white object-cover">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>

                        {{-- Navigation --}}
                        <nav class="space-y-1">
                            <a href="{{ route('dashboard') }}"
                               class="nav-item {{ $isActive('dashboard') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                {{ __('Dashboard') }}
                            </a>

                            <div class="pt-4 pb-2 px-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Shopping') }}</p>
                            </div>

                            <a href="{{ route('account.orders') }}"
                               class="nav-item {{ $isActive('account.orders') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                {{ __('My Orders') }}
                                @if($pendingOrders > 0)
                                    <span class="ml-auto px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-700 rounded-full">{{ $pendingOrders }}</span>
                                @endif
                            </a>

                            <a href="{{ route('account.wishlist') }}"
                               class="nav-item {{ $isActive('account.wishlist') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                {{ __('Wishlist') }}
                                @if($wishlistCount > 0)
                                    <span class="ml-auto px-2 py-0.5 text-xs font-bold bg-pink-100 text-pink-700 rounded-full">{{ $wishlistCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('account.reviews') }}"
                               class="nav-item {{ $isActive('account.reviews') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                {{ __('My Reviews') }}
                            </a>

                            <div class="pt-4 pb-2 px-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Account') }}</p>
                            </div>

                            <a href="{{ route('account.wallet') }}"
                               class="nav-item {{ $isActive('account.wallet') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                {{ __('Wallet') }}
                            </a>

                            <a href="{{ route('account.addresses') }}"
                               class="nav-item {{ $isActive('account.addresses') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('Addresses') }}
                            </a>

                            <a href="{{ route('account.profile') }}"
                               class="nav-item {{ $isActive('account.profile') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                {{ __('Profile') }}
                            </a>

                            <div class="pt-4 pb-2 px-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('Settings') }}</p>
                            </div>

                            <a href="{{ route('account.password') }}"
                               class="nav-item {{ $isActive('account.password') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ __('Change Password') }}
                            </a>

                            <a href="{{ route('account.notifications') }}"
                               class="nav-item {{ $isActive('account.notifications') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                {{ __('Notifications') }}
                            </a>

                            <a href="{{ route('account.recently-viewed') }}"
                               class="nav-item {{ $isActive('account.recently-viewed') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ __('Recently Viewed') }}
                            </a>

                            <div class="pt-4 pb-2 px-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ __('More') }}</p>
                            </div>

                            <a href="{{ route('account.settings') }}"
                               class="nav-item {{ $isActive('account.settings') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ __('Account Settings') }}
                            </a>

                            {{-- Cart Quick Link --}}
                            <a href="{{ route('cart.index') }}"
                               class="nav-item {{ $isActive('cart.*') }}">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                                </svg>
                                {{ __('Shopping Cart') }}
                                @if($cartCount > 0)
                                    <span class="ml-auto px-2 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full">{{ $cartCount }}</span>
                                @endif
                            </a>

                            {{-- Logout --}}
                            <div class="pt-4 mt-2 border-t border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-item w-full text-red-500 hover:bg-red-50 hover:text-red-600">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </div>
                        </nav>
                    </div>
                </aside>

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

</x-storefront.layout>
