<x-storefront.layout title="{{ __('Premium Marketplace') }}">

    {{-- ═══════════════════════ ANNOUNCEMENT BAR ═══════════════════════ --}}
    <div class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 text-white" x-data="{ show: true }" x-show="show" x-transition x-cloak>
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-center gap-3">
            <span class="text-[12px] font-medium tracking-wide">{{ __('Free shipping on orders over $50') }}</span>
            <span class="w-1 h-1 bg-white/50 rounded-full"></span>
            <span class="text-[12px] font-medium tracking-wide">{{ __('Use code') }} <strong>PREMIUM10</strong> {{ __('for 10% off') }}</span>
            <button @click="show = false" class="ml-3 text-white/70 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════ HEADER / NAVBAR ═══════════════════════ --}}
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2.5 flex-shrink-0">
                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-[18px] font-bold text-gray-900 tracking-tight hidden sm:block">{{ config('app.name', 'Marketplace') }}</span>
                </a>

                {{-- Search --}}
                <form method="GET" action="{{ route('search') }}" class="hidden md:flex flex-1 max-w-xl mx-8">
                    <div class="relative w-full">
                        <input type="text" name="keyword" placeholder="{{ __('Search for products, brands, categories...') }}"
                               class="w-full pl-11 pr-4 py-2.5 text-[13px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-50 transition-all placeholder:text-gray-400">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>

                {{-- Right Actions --}}
                <div class="flex items-center gap-1">
                    {{-- Language --}}
                    <button class="hidden sm:flex items-center gap-1 px-2.5 py-2 text-[12px] font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        EN
                    </button>

                    {{-- Wishlist --}}
                    <button class="relative p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">0</span>
                    </button>

                    {{-- Cart --}}
                    <button @click="$store.cart.open = !$store.cart.open" class="relative p-2.5 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-xl transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-emerald-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center"
                              x-text="$store.cart.count > 0 ? $store.cart.count : '0'"
                              x-show="$store.cart.count > 0"></span>
                    </button>

                    {{-- Account --}}
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hidden sm:flex items-center gap-2 px-3 py-2 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                            <img src="{{ auth()->user()->avatar_url }}" class="w-6 h-6 rounded-full ring-2 ring-gray-100" alt="">
                            <span class="max-w-[80px] truncate">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-1.5 px-3.5 py-2 text-[13px] font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ __('Login') }}
                        </a>
                    @endauth

                    {{-- Mobile Toggle --}}
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-gray-500 hover:bg-gray-50 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            {{-- Categories Bar --}}
            <div class="hidden md:flex items-center gap-1 -mb-px overflow-x-auto pb-px scrollbar-none">
                @foreach($categories as $cat)
                    <a href="{{ route('search', ['category_id' => $cat->id]) }}"
                       class="flex-shrink-0 px-3 py-2 text-[12px] font-medium text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all whitespace-nowrap">
                        {{ app()->getLocale() === 'bn' && $cat->name_bn ? $cat->name_bn : $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-white border-t border-gray-100 shadow-xl">
            <div class="max-w-7xl mx-auto px-4 py-4 space-y-2">
                <form method="GET" action="{{ route('search') }}" class="mb-3">
                    <div class="relative">
                        <input type="text" name="keyword" placeholder="{{ __('Search products...') }}" class="w-full pl-10 pr-4 py-2.5 text-[13px] bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>
                @foreach($categories as $cat)
                    <a href="{{ route('search', ['category_id' => $cat->id]) }}" class="block px-3 py-2 text-[13px] font-medium text-gray-600 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition-colors">{{ app()->getLocale() === 'bn' && $cat->name_bn ? $cat->name_bn : $cat->name }}</a>
                @endforeach
            </div>
        </div>
    </header>

    {{-- ═══════════════════════ HERO SLIDER ═══════════════════════ --}}
    <section class="relative bg-white overflow-hidden" x-data="heroSlider()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 py-6 lg:py-10">
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900" style="min-height: 420px;">
                @php
                    $slides = [
                        ['title' => __('Premium Marketplace'), 'subtitle' => __('Discover exceptional products from verified sellers worldwide'), 'cta' => __('Shop Now'), 'gradient' => 'from-emerald-600/90 to-teal-600/90', 'accent' => 'bg-white/10', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        ['title' => __('Flash Sale Event'), 'subtitle' => __('Up to 70% off on thousands of premium products'), 'cta' => __('View Deals'), 'gradient' => 'from-rose-600/90 to-orange-600/90', 'accent' => 'bg-white/10', 'icon' => 'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z'],
                        ['title' => __('New Arrivals Daily'), 'subtitle' => __('Fresh inventory from top brands added every day'), 'cta' => __('Explore'), 'gradient' => 'from-violet-600/90 to-indigo-600/90', 'accent' => 'bg-white/10', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                    ];
                @endphp

                @foreach($slides as $i => $slide)
                    <div x-show="current === {{ $i }}"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 scale-105"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-500"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute inset-0 slide-content"
                         style="display: none;">
                        <div class="absolute inset-0 bg-gradient-to-r {{ $slide['gradient'] }}"></div>
                        <div class="absolute inset-0 {{ $slide['accent'] }}">
                            <svg class="absolute right-0 top-0 w-[600px] h-[600px] opacity-10" fill="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="relative z-10 flex items-center h-full min-h-[420px] px-8 lg:px-16">
                            <div class="max-w-lg">
                                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6 hero-icon">
                                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $slide['icon'] }}"/></svg>
                                </div>
                                <h2 class="text-3xl lg:text-5xl font-bold text-white leading-tight mb-4 hero-title">{{ $slide['title'] }}</h2>
                                <p class="text-[15px] text-white/80 mb-8 leading-relaxed hero-subtitle">{{ $slide['subtitle'] }}</p>
                                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-gray-900 text-[14px] font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-xl shadow-black/10 hover:shadow-2xl hover:-translate-y-0.5 hero-cta">
                                    {{ $slide['cta'] }}
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Navigation Arrows --}}
                <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-all z-20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-all z-20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>

                {{-- Pagination --}}
                <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
                    @foreach($slides as $i => $slide)
                        <button @click="goTo({{ $i }})"
                                :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/60'"
                                class="h-2.5 rounded-full transition-all duration-300"></button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ FEATURED CATEGORIES ═══════════════════════ --}}
    @if($categories->count())
    <section class="py-12 lg:py-16" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Browse')"
                :subtitle="__('Shop by Category')"
                accent="emerald"
                :viewAllHref="route('search')"
                :viewAllText="__('View All')"
            />
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($categories->take(6) as $category)
                    <x-storefront.category-card :category="$category" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ FLASH SALE ═══════════════════════ --}}
    @if($flashSaleProducts->count())
    <section class="py-12 lg:py-16 bg-white" data-gsap="section" x-data="flashSaleTimer()">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <p class="text-[11px] font-bold text-red-500 uppercase tracking-widest mb-1">{{ __('Limited Time') }}</p>
                    <h2 class="text-[22px] font-bold text-gray-900">{{ __('Flash Sale') }}</h2>
                </div>
                <div class="flex items-center gap-4">
                    {{-- Countdown Timer --}}
                    <div class="hidden sm:flex items-center gap-2" x-show="!expired">
                        <div class="text-center">
                            <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                                <span class="text-white text-[14px] font-bold" x-text="hours">00</span>
                            </div>
                            <span class="text-[9px] text-gray-400 mt-1 block">{{ __('HRS') }}</span>
                        </div>
                        <span class="text-gray-900 font-bold text-lg">:</span>
                        <div class="text-center">
                            <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                                <span class="text-white text-[14px] font-bold" x-text="minutes">00</span>
                            </div>
                            <span class="text-[9px] text-gray-400 mt-1 block">{{ __('MIN') }}</span>
                        </div>
                        <span class="text-gray-900 font-bold text-lg">:</span>
                        <div class="text-center">
                            <div class="w-10 h-10 bg-gray-900 rounded-lg flex items-center justify-center">
                                <span class="text-white text-[14px] font-bold" x-text="seconds">00</span>
                            </div>
                            <span class="text-[9px] text-gray-400 mt-1 block">{{ __('SEC') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('search') }}" class="hidden sm:inline-flex items-center gap-1 text-[13px] font-semibold text-red-500 hover:text-red-600 transition-colors">
                        {{ __('View All Deals') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($flashSaleProducts->take(4) as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ PROMOTIONAL BANNERS ═══════════════════════ --}}
    <section class="py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-storefront.banner
                    :href="route('search')"
                    :tagline="__('New Collection')"
                    :title="__('Trending Products')"
                    :description="__('Discover what\'s hot right now in the marketplace')"
                    :cta="__('Shop Now')"
                    gradient="from-emerald-500 to-teal-600"
                />
                <x-storefront.banner
                    :href="route('search')"
                    :tagline="__('Top Brands')"
                    :title="__('Premium Sellers')"
                    :description="__('Shop from our most trusted and featured sellers')"
                    :cta="__('Explore')"
                    gradient="from-violet-500 to-purple-600"
                />
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ TRENDING PRODUCTS ═══════════════════════ --}}
    @if($trendingProducts->count())
    <section class="py-12 lg:py-16" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Hot Right Now')"
                :subtitle="__('Trending Products')"
                accent="indigo"
                :viewAllHref="route('search')"
                :viewAllText="__('View All')"
            />
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($trendingProducts->take(4) as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ FEATURED SELLERS ═══════════════════════ --}}
    @if($featuredSellers->count())
    <section class="py-12 lg:py-16 bg-white" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Verified')"
                :subtitle="__('Featured Sellers')"
                accent="amber"
            />
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($featuredSellers as $seller)
                    <x-storefront.seller-card :seller="$seller" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ NEW ARRIVALS ═══════════════════════ --}}
    @if($newProducts->count())
    <section class="py-12 lg:py-16" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Just Dropped')"
                :subtitle="__('New Arrivals')"
                accent="violet"
                :viewAllHref="route('search', ['sort' => 'newest'])"
                :viewAllText="__('View All')"
            />
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($newProducts->take(4) as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ BEST SELLING PRODUCTS ═══════════════════════ --}}
    @if($bestSellingProducts->count())
    <section class="py-12 lg:py-16 bg-white" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Customer Favorites')"
                :subtitle="__('Best Selling Products')"
                accent="emerald"
                :viewAllHref="route('search', ['sort' => 'best_selling'])"
                :viewAllText="__('View All')"
            />
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($bestSellingProducts->take(4) as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ SECOND PROMOTIONAL BANNER ═══════════════════════ --}}
    <section class="py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-storefront.banner
                    :href="route('search')"
                    :tagline="__('Special Offer')"
                    :title="__('Deals of the Day')"
                    :description="__('Handpicked products with the biggest discounts')"
                    :cta="__('Shop Deals')"
                    gradient="from-rose-500 to-pink-600"
                />
                <x-storefront.banner
                    :href="route('search')"
                    :tagline="__('Quality Assured')"
                    :title="__('Verified Products')"
                    :description="__('Every product from our trusted verified sellers')"
                    :cta="__('Browse')"
                    gradient="from-amber-500 to-orange-600"
                />
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ TOP BRANDS ═══════════════════════ --}}
    @if($topBrands->count())
    <section class="py-12 lg:py-16" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-8">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('Trusted by Millions') }}</p>
                <h2 class="text-[22px] font-bold text-gray-900">{{ __('Top Brands') }}</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                @foreach($topBrands as $brand)
                    <a href="{{ route('search', ['brand_id' => $brand->id]) }}" class="flex items-center justify-center p-5 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group" data-gsap="category">
                        @if($brand->logo)
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" loading="lazy" decoding="async" class="max-h-8 max-w-full object-contain opacity-60 group-hover:opacity-100 transition-opacity">
                        @else
                            <span class="text-[13px] font-semibold text-gray-400 group-hover:text-gray-900 transition-colors">{{ app()->getLocale() === 'bn' && $brand->name_bn ? $brand->name_bn : $brand->name }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════ RECOMMENDED FOR YOU (AI Integration Ready) ═══════════════════════ --}}
    <section class="py-12 lg:py-16 bg-white" data-gsap="section" x-data="{ loaded: false }" x-intersect.once="loaded = true">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Curated for You')"
                :subtitle="__('Recommended Products')"
                accent="emerald"
                :viewAllHref="route('search')"
                :viewAllText="__('View All')"
            />
            <div x-show="loaded" x-transition>
                @if($featuredProducts->count())
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($featuredProducts->take(4) as $product)
                            <x-storefront.product-card :product="$product" />
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <p class="text-[14px] text-gray-400">{{ __('Personalized recommendations coming soon') }}</p>
                    </div>
                @endif
            </div>
            <div x-show="!loaded" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach(range(1, 4) as $skeleton)
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                        <div class="aspect-square shimmer"></div>
                        <div class="p-4 space-y-2">
                            <div class="h-3 shimmer rounded w-1/3"></div>
                            <div class="h-4 shimmer rounded w-2/3"></div>
                            <div class="h-3 shimmer rounded w-1/2"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ RECENTLY VIEWED PRODUCTS ═══════════════════════ --}}
    <section class="py-12 lg:py-16" data-gsap="section" x-data="recentlyViewed()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4">
            <x-storefront.section-header
                :title="__('Continue Shopping')"
                :subtitle="__('Recently Viewed')"
                accent="gray"
                :viewAllHref="route('search')"
                :viewAllText="__('View All')"
            />
            <div x-show="products.length > 0" x-transition>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="product in products" :key="product.slug">
                        <a :href="'/product/' + product.slug" class="group relative bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-500 hover:-translate-y-1">
                            <div class="relative aspect-square bg-gray-50 overflow-hidden">
                                <img :src="product.image" :alt="product.name" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                            </div>
                            <div class="p-4">
                                <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2 leading-snug group-hover:text-emerald-600 transition-colors" x-text="product.name"></h3>
                                <div class="flex items-baseline gap-2 mt-2">
                                    <span class="text-[17px] font-bold text-gray-900" x-text="'$' + parseFloat(product.price).toFixed(2)"></span>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
            <div x-show="products.length === 0" x-transition class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[14px] text-gray-400">{{ __('Your recently viewed products will appear here') }}</p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ TRUST SECTION ═══════════════════════ --}}
    <section class="py-12 lg:py-16 bg-white" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-[22px] font-bold text-gray-900">{{ __('Why Shop With Us') }}</h2>
                <p class="text-[14px] text-gray-500 mt-2">{{ __('Your satisfaction is our priority') }}</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Secure Payment --}}
                <div class="text-center group" data-gsap="category">
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-100 transition-colors duration-300 group-hover:scale-110 transform">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">{{ __('Secure Payment') }}</h3>
                    <p class="text-[12px] text-gray-500">{{ __('100% secure payment processing') }}</p>
                </div>

                {{-- Cash on Delivery --}}
                <div class="text-center group" data-gsap="category">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-100 transition-colors duration-300 group-hover:scale-110 transform">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">{{ __('Cash on Delivery') }}</h3>
                    <p class="text-[12px] text-gray-500">{{ __('Pay when you receive your order') }}</p>
                </div>

                {{-- Fast Delivery --}}
                <div class="text-center group" data-gsap="category">
                    <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-amber-100 transition-colors duration-300 group-hover:scale-110 transform">
                        <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">{{ __('Fast Delivery') }}</h3>
                    <p class="text-[12px] text-gray-500">{{ __('Express shipping available nationwide') }}</p>
                </div>

                {{-- 24/7 Support --}}
                <div class="text-center group" data-gsap="category">
                    <div class="w-16 h-16 bg-violet-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-violet-100 transition-colors duration-300 group-hover:scale-110 transform">
                        <svg class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3 class="text-[14px] font-semibold text-gray-900 mb-1">{{ __('24/7 Support') }}</h3>
                    <p class="text-[12px] text-gray-500">{{ __('Dedicated customer support team') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ NEWSLETTER ═══════════════════════ --}}
    <section class="py-16 lg:py-20" data-gsap="section">
        <div class="max-w-7xl mx-auto px-4">
            <div class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl p-10 lg:p-16 overflow-hidden text-center">
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </div>
                <div class="relative z-10 max-w-lg mx-auto">
                    <h2 class="text-2xl lg:text-3xl font-bold text-white mb-3">{{ __('Stay in the Loop') }}</h2>
                    <p class="text-[14px] text-gray-400 mb-8">{{ __('Subscribe to our newsletter for exclusive deals, new arrivals, and insider tips.') }}</p>
                    <form class="flex gap-2 max-w-md mx-auto" onsubmit="return false;">
                        <input type="email" placeholder="{{ __('Enter your email') }}" class="flex-1 px-5 py-3.5 bg-white/10 border border-white/10 rounded-xl text-white placeholder-gray-500 text-[13px] focus:outline-none focus:border-emerald-500 focus:bg-white/15 transition-all">
                        <button type="submit" class="px-6 py-3.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-500 transition-all shadow-lg shadow-emerald-600/20 whitespace-nowrap">{{ __('Subscribe') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ FOOTER ═══════════════════════ --}}
    <footer class="bg-gray-900 text-gray-400">
        <div class="max-w-7xl mx-auto px-4 py-16">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 lg:gap-12">
                <div class="col-span-2 lg:col-span-1">
                    <a href="/" class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4.5 h-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-[16px] font-bold text-white">{{ config('app.name', 'Marketplace') }}</span>
                    </a>
                    <p class="text-[12.5px] text-gray-500 leading-relaxed">{{ __('Your premium destination for quality products from verified sellers worldwide.') }}</p>
                    <div class="flex items-center gap-3 mt-5">
                        <a href="#" class="w-9 h-9 bg-white/5 hover:bg-emerald-600 rounded-lg flex items-center justify-center text-gray-500 hover:text-white transition-all"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                        <a href="#" class="w-9 h-9 bg-white/5 hover:bg-emerald-600 rounded-lg flex items-center justify-center text-gray-500 hover:text-white transition-all"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                        <a href="#" class="w-9 h-9 bg-white/5 hover:bg-emerald-600 rounded-lg flex items-center justify-center text-gray-500 hover:text-white transition-all"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-[12px] font-bold text-white uppercase tracking-wider mb-4">{{ __('Quick Links') }}</h4>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('search') }}" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('All Products') }}</a></li>
                        <li><a href="{{ route('search', ['sort' => 'newest']) }}" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('New Arrivals') }}</a></li>
                        <li><a href="{{ route('search', ['sort' => 'best_selling']) }}" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Best Sellers') }}</a></li>
                        <li><a href="{{ route('search') }}" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Sale') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[12px] font-bold text-white uppercase tracking-wider mb-4">{{ __('Categories') }}</h4>
                    <ul class="space-y-2.5">
                        @foreach($categories->take(5) as $cat)
                            <li><a href="{{ route('search', ['category_id' => $cat->id]) }}" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ app()->getLocale() === 'bn' && $cat->name_bn ? $cat->name_bn : $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="text-[12px] font-bold text-white uppercase tracking-wider mb-4">{{ __('Support') }}</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Help Center') }}</a></li>
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Shipping Info') }}</a></li>
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Returns') }}</a></li>
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Contact Us') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[12px] font-bold text-white uppercase tracking-wider mb-4">{{ __('Company') }}</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('About Us') }}</a></li>
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Careers') }}</a></li>
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Privacy Policy') }}</a></li>
                        <li><a href="#" class="text-[13px] text-gray-500 hover:text-emerald-400 transition-colors">{{ __('Terms of Service') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-[12px] text-gray-600">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                <div class="flex items-center gap-4">
                    <span class="text-[11px] text-gray-600">{{ __('We accept') }}:</span>
                    <div class="flex items-center gap-2">
                        <div class="px-2 py-1 bg-white/5 rounded text-[10px] font-bold text-gray-500">VISA</div>
                        <div class="px-2 py-1 bg-white/5 rounded text-[10px] font-bold text-gray-500">MC</div>
                        <div class="px-2 py-1 bg-white/5 rounded text-[10px] font-bold text-gray-500">PayPal</div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════ SCRIPTS ═══════════════════════ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script>
        function heroSlider() {
            return {
                current: 0,
                total: 3,
                interval: null,
                init() {
                    this.startAutoPlay();
                    this.$nextTick(() => this.animateSlide());
                },
                startAutoPlay() {
                    this.interval = setInterval(() => this.next(), 6000);
                },
                stopAutoPlay() {
                    clearInterval(this.interval);
                },
                next() {
                    this.current = (this.current + 1) % this.total;
                    this.animateSlide();
                },
                prev() {
                    this.current = (this.current - 1 + this.total) % this.total;
                    this.animateSlide();
                },
                goTo(i) {
                    this.current = i;
                    this.stopAutoPlay();
                    this.startAutoPlay();
                    this.animateSlide();
                },
                animateSlide() {
                    this.$nextTick(() => {
                        const active = document.querySelector('.slide-content[style*="display: block"], .slide-content:not([style*="display: none"])');
                        if (active && typeof gsap !== 'undefined') {
                            gsap.fromTo(active.querySelectorAll('.hero-icon, .hero-title, .hero-subtitle, .hero-cta'),
                                { opacity: 0, y: 30 },
                                { opacity: 1, y: 0, duration: 0.8, stagger: 0.12, ease: 'power3.out', delay: 0.2 }
                            );
                        }
                    });
                }
            };
        }

        function flashSaleTimer() {
            const now = new Date();
            const end = new Date(now);
            end.setHours(23, 59, 59, 999);
            const diff = Math.max(0, Math.floor((end - now) / 1000));

            return {
                total: diff,
                hours: String(Math.floor(diff / 3600)).padStart(2, '0'),
                minutes: String(Math.floor((diff % 3600) / 60)).padStart(2, '0'),
                seconds: String(diff % 60).padStart(2, '0'),
                expired: diff <= 0,
                init() {
                    if (this.expired) return;
                    setInterval(() => {
                        if (this.total <= 0) { this.expired = true; return; }
                        this.total--;
                        this.hours = String(Math.floor(this.total / 3600)).padStart(2, '0');
                        this.minutes = String(Math.floor((this.total % 3600) / 60)).padStart(2, '0');
                        this.seconds = String(this.total % 60).padStart(2, '0');
                    }, 1000);
                }
            };
        }

        function recentlyViewed() {
            return {
                products: [],
                init() {
                    try {
                        const stored = localStorage.getItem('recently_viewed');
                        this.products = stored ? JSON.parse(stored).slice(0, 4) : [];
                    } catch (e) { this.products = []; }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

            gsap.registerPlugin(ScrollTrigger);

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) return;

            gsap.utils.toArray('[data-gsap="section"]').forEach(section => {
                gsap.fromTo(section,
                    { opacity: 0, y: 40 },
                    { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out',
                      scrollTrigger: { trigger: section, start: 'top 85%', once: true } }
                );
            });

            gsap.utils.toArray('[data-gsap="product"], [data-gsap="category"]').forEach((el, i) => {
                gsap.fromTo(el,
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.6, ease: 'power3.out', delay: (i % 4) * 0.1,
                      scrollTrigger: { trigger: el, start: 'top 90%', once: true } }
                );
            });

            gsap.fromTo('header',
                { opacity: 0, y: -20 },
                { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out', delay: 0.1 }
            );

            gsap.fromTo('.hero-slide',
                { opacity: 0, scale: 0.98 },
                { opacity: 1, scale: 1, duration: 1, ease: 'power3.out', delay: 0.3 }
            );

            gsap.utils.toArray('.product-card').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    gsap.to(card, { scale: 1.02, duration: 0.3, ease: 'power2.out' });
                });
                card.addEventListener('mouseleave', () => {
                    gsap.to(card, { scale: 1, duration: 0.3, ease: 'power2.out' });
                });
            });

            gsap.utils.toArray('.category-card').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    gsap.to(card, { scale: 1.03, duration: 0.3, ease: 'power2.out' });
                });
                card.addEventListener('mouseleave', () => {
                    gsap.to(card, { scale: 1, duration: 0.3, ease: 'power2.out' });
                });
            });
        });
    </script>
    @endpush

</x-storefront.layout>
