@props(['categories' => [], 'brands' => collect()])

@php
    $displayCategories = $categories->take(12);
@endphp

<header
    class="sticky top-0 z-50 transition-all duration-300"
    x-data="{
        mobileOpen: false,
        megaOpen: false,
        hoveredCategory: null,
        scrolled: false,
        searchFocused: false,
        searchQuery: '',
        megaLeaveTimeout: null,
        categoryLeaveTimeout: null,
        megaEnter() {
            clearTimeout(this.megaLeaveTimeout);
            this.megaOpen = true;
        },
        megaLeave() {
            this.megaLeaveTimeout = setTimeout(() => {
                this.megaOpen = false;
                this.hoveredCategory = null;
            }, 120);
        },
        megaPanelEnter() {
            clearTimeout(this.megaLeaveTimeout);
        },
        megaPanelLeave() {
            this.megaLeave();
        },
        categoryEnter(cat) {
            clearTimeout(this.categoryLeaveTimeout);
            this.hoveredCategory = cat;
        },
        categoryLeave() {
            this.categoryLeaveTimeout = setTimeout(() => {
                this.hoveredCategory = null;
            }, 80);
        },
        categoryPanelEnter() {
            clearTimeout(this.categoryLeaveTimeout);
        },
        categoryPanelLeave() {
            this.categoryLeave();
        }
    }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
    :class="scrolled ? 'bg-white/95 backdrop-blur-xl shadow-lg shadow-black/[0.04] border-b border-gray-100' : 'bg-white/80 backdrop-blur-xl border-b border-gray-100'"
>
    {{-- Top Bar --}}
    <div class="border-b border-white/20 bg-emerald-500" x-show="!scrolled" x-transition.opacity.duration.300ms>
        <div class="max-w-[1400px] mx-auto px-4 lg:px-6 py-2 flex items-center justify-between text-[12px] text-white/90">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <span class="font-medium">{{ __('Free shipping on orders over $50') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <span class="font-medium">{{ __('Use code') }} <strong>PREMIUM10</strong> {{ __('for 10% off') }}</span>
            </div>
        </div>
    </div>

    {{-- Main Header --}}
    <div class="max-w-[1400px] mx-auto px-4 lg:px-6">
        <div class="flex items-center justify-between h-[64px] gap-5">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 flex-shrink-0 group">
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25 group-hover:shadow-emerald-500/40 transition-all duration-300 group-hover:scale-105">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-[20px] font-bold text-gray-900 tracking-tight hidden sm:block">{{ config('app.name', 'Ecommerce') }}</span>
            </a>

            <form method="GET" action="{{ route('search') }}" class="hidden md:flex flex-1 max-w-2xl mx-6" @submit.prevent="if(searchQuery.trim()) this.$el.submit()">
                <div class="relative w-full" :class="searchFocused && 'ring-4 ring-emerald-50/80'">
                    <input type="text" name="keyword" x-model="searchQuery" @focus="searchFocused = true" @blur="searchFocused = false" placeholder="{{ __('Search for products, brands, categories...') }}" class="w-full pl-12 pr-4 py-3 text-[14px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 focus:bg-white transition-all placeholder:text-gray-400">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

            <div class="flex items-center gap-1.5">
                <button class="hidden sm:flex items-center gap-1.5 px-3 py-2 text-[13px] font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    EN
                </button>
                @auth
                    <a href="{{ route('wishlist.index') }}" class="relative p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span x-data="wishlistCount()" x-init="init()" x-show="count > 0" x-text="count" class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center"></span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </a>
                @endauth
                <button @click="$store.cart.open = !$store.cart.open" class="relative p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <span class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 bg-emerald-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center" x-text="$store.cart.count > 0 ? $store.cart.count : '0'" x-show="$store.cart.count > 0"></span>
                </button>
                @auth
                    <a href="{{ url('/dashboard') }}" class="hidden sm:flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="max-w-[80px] truncate">{{ auth()->user()->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-2 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ __('Login') }}
                    </a>
                @endauth
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-gray-500 hover:bg-gray-50 rounded-xl">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Navigation --}}
    <div class="border-t border-gray-100/80">
        <div class="max-w-[1400px] mx-auto px-4 lg:px-6">
            <nav class="hidden lg:flex items-center gap-0.5 h-11">
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    {{ __('Home') }}
                </a>

                {{-- Category Mega Menu Trigger --}}
                <div class="relative" @mouseenter="megaEnter()" @mouseleave="megaLeave()">
                    <button class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-semibold text-white bg-emerald-500 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        {{ __('Category') }}
                        <svg class="w-3 h-3 transition-transform duration-200" :class="megaOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    {{-- MEGA MENU PANEL --}}
                    <div
                        x-show="megaOpen" x-cloak
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-1"
                        @mouseenter="megaPanelEnter()" @mouseleave="megaPanelLeave()"
                        @click.away="megaOpen = false"
                        class="absolute top-full left-0 w-[680px] mt-0 z-50"
                    >
                        <div class="bg-white rounded-2xl shadow-[0_20px_60px_-12px_rgba(0,0,0,0.15)] border border-gray-100 overflow-hidden">
                            <div class="flex min-h-[400px]">

                                {{-- LEFT: Category List --}}
                                <div class="w-[260px] flex-shrink-0 border-r border-gray-100 flex flex-col">
                                    <a href="{{ route('search') }}" class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 hover:bg-gray-50 transition-colors group">
                                        <div class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                            <span class="text-[14px] font-bold text-gray-800">{{ __('All Categories') }}</span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                    <div class="flex-1 overflow-y-auto py-1" style="scrollbar-width:thin">
                                        @foreach($displayCategories as $cat)
                                            <a
                                                href="{{ route('search', ['category_id' => $cat->id]) }}"
                                                @mouseenter="categoryEnter({{ json_encode(['id' => $cat->id, 'name' => app()->getLocale() === 'bn' && $cat->name_bn ? $cat->name_bn : $cat->name, 'children' => $cat->children ? $cat->children->take(8)->map(fn($c) => ['id' => $c->id, 'name' => app()->getLocale() === 'bn' && $c->name_bn ? $c->name_bn : $c->name])->values() : []]) }}"
                                                @mouseleave="categoryLeave()"
                                                class="flex items-center gap-3 px-5 py-2.5 text-[13px] transition-all duration-150 group"
                                                :class="hoveredCategory && hoveredCategory.id === {{ $cat->id }} ? 'bg-emerald-50 text-emerald-600' : 'text-gray-600 hover:bg-gray-50'"
                                            >
                                                @if($cat->image)
                                                    <img src="{{ asset('storage/' . $cat->image) }}" alt="" class="w-7 h-7 rounded-lg object-cover bg-white shadow-sm flex-shrink-0">
                                                @else
                                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                    </div>
                                                @endif
                                                <span class="flex-1 font-medium">{{ app()->getLocale() === 'bn' && $cat->name_bn ? $cat->name_bn : $cat->name }}</span>
                                                @if($cat->children && $cat->children->count())
                                                    <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- RIGHT: Subcategories --}}
                                <div
                                    class="flex-1 p-5"
                                    @mouseenter="categoryPanelEnter()"
                                    @mouseleave="categoryPanelLeave()"
                                >
                                    <template x-if="hoveredCategory && hoveredCategory.children && hoveredCategory.children.length > 0">
                                        <div>
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-[15px] font-bold text-gray-900" x-text="hoveredCategory.name"></h3>
                                                <a :href="'{{ route('search') }}?category_id=' + hoveredCategory.id" class="text-[12px] font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-0.5">
                                                    {{ __('View All') }}
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            </div>
                                            <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                                                <template x-for="child in hoveredCategory.children" :key="child.id">
                                                    <a :href="'{{ route('search') }}?category_id=' + child.id" class="flex items-center justify-between py-2 px-2.5 text-[13px] text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/60 rounded-lg transition-colors group">
                                                        <span x-text="child.name"></span>
                                                        <svg class="w-3 h-3 text-gray-300 group-hover:text-emerald-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                    </a>
                                                </template>
                                            </div>
                                            <a :href="'{{ route('search') }}?category_id=' + hoveredCategory.id" class="mt-4 flex items-center justify-center gap-2 w-full py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[13px] font-semibold rounded-xl transition-colors">
                                                {{ __('Browse all') }} <span x-text="hoveredCategory.name"></span>
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                            </a>
                                        </div>
                                    </template>
                                    <template x-if="!hoveredCategory || !hoveredCategory.children || hoveredCategory.children.length === 0">
                                        <div class="flex flex-col items-center justify-center h-full text-center py-12">
                                            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4">
                                                <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                            </div>
                                            <p class="text-[13px] text-gray-400 font-medium">{{ __('Hover over a category to see subcategories') }}</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('search') }}" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    {{ __('Shop') }}
                </a>
                <a href="{{ route('search', ['sort' => 'newest']) }}" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    {{ __('New Arrivals') }}
                </a>
                <a href="{{ route('search', ['sort' => 'popular']) }}" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    {{ __('Best Sellers') }}
                </a>
                <a href="#" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    {{ __('Blog') }}
                </a>
                <a href="#" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    {{ __('About Us') }}
                </a>
                <a href="#" class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-gray-600 hover:text-emerald-600 hover:bg-emerald-50/80 rounded-lg transition-all">
                    {{ __('Contact Us') }}
                </a>
            </nav>
        </div>
    </div>

    {{-- MOBILE MENU --}}
    <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden bg-white border-t border-gray-100 shadow-xl max-h-[80vh] overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <form method="GET" action="{{ route('search') }}" class="mb-4">
                <div class="relative">
                    <input type="text" name="keyword" placeholder="{{ __('Search products...') }}" class="w-full pl-10 pr-4 py-2.5 text-[13px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('Home') }}</a>
                <a href="{{ route('search') }}" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('Shop All') }}</a>
                @foreach($displayCategories as $cat)
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">
                            <span class="flex items-center gap-2">
                                @if($cat->image)<img src="{{ asset('storage/' . $cat->image) }}" alt="" class="w-6 h-6 rounded-md object-cover">@else<div class="w-6 h-6 rounded-md bg-emerald-100 flex items-center justify-center"><svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>@endif
                                {{ app()->getLocale() === 'bn' && $cat->name_bn ? $cat->name_bn : $cat->name }}
                            </span>
                            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-collapse class="pl-4 pb-1">
                            @if($cat->children && $cat->children->count())
                                @foreach($cat->children as $child)
                                    <a href="{{ route('search', ['category_id' => $child->id]) }}" class="block px-3 py-2 text-[12px] text-gray-500 hover:text-emerald-600 transition-colors">{{ app()->getLocale() === 'bn' && $child->name_bn ? $child->name_bn : $child->name }}</a>
                                @endforeach
                            @endif
                            <a href="{{ route('search', ['category_id' => $cat->id]) }}" class="block px-3 py-2 text-[12px] font-medium text-emerald-600">{{ __('View All') }}</a>
                        </div>
                    </div>
                @endforeach
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <a href="{{ route('search', ['sort' => 'newest']) }}" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('New Arrivals') }}</a>
                    <a href="{{ route('search', ['sort' => 'popular']) }}" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('Best Sellers') }}</a>
                    <a href="#" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('Blog') }}</a>
                    <a href="#" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('About Us') }}</a>
                    <a href="#" class="block px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ __('Contact Us') }}</a>
                </div>
                @if($brands->count())
                    <div class="border-t border-gray-100 mt-2 pt-3">
                        <p class="px-3 text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">{{ __('Popular Brands') }}</p>
                        <div class="flex flex-wrap gap-2 px-3">
                            @foreach($brands->take(6) as $brand)
                                <a href="{{ route('search', ['brand_id' => $brand->id]) }}" class="px-2.5 py-1.5 bg-gray-50 rounded-lg text-[11px] font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">{{ $brand->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="border-t border-gray-100 mt-2 pt-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"><img src="{{ auth()->user()->avatar_url }}" class="w-7 h-7 rounded-full ring-2 ring-gray-100" alt="">{{ auth()->user()->name }}</a>
                        <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            {{ __('My Orders') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-2 px-3 py-2.5 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>{{ __('Login') }}</a>
                        <a href="{{ route('register') }}" class="flex items-center gap-2 px-3 py-2.5 text-[13px] font-medium text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>{{ __('Create Account') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</header>
