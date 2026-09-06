<x-storefront.layout title="{{ __('Search Products') }}">

    {{-- ═══════════════════════ SEARCH HEADER ═══════════════════════ --}}
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 overflow-hidden" data-gsap="section">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/20 to-transparent"></div>
        <div class="relative max-w-7xl mx-auto px-4 py-12 lg:py-16">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div data-gsap="category">
                    <nav class="flex items-center gap-2 mb-4 text-[12px] text-gray-400">
                        <a href="{{ route('home') }}" class="hover:text-emerald-400 transition-colors">{{ __('Home') }}</a>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-white">{{ __('Search') }}</span>
                    </nav>
                    <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2">
                        @if(!empty($filters['keyword']))
                            {{ __('Search results for') }} "<span class="text-emerald-400">{{ $filters['keyword'] }}</span>"
                        @else
                            {{ __('All Products') }}
                        @endif
                    </h1>
                    <p class="text-[13px] text-gray-500 mt-2">{{ $results->total() }} {{ __('products found') }}</p>
                </div>

                {{-- Search form --}}
                <form method="GET" action="{{ route('search') }}" class="w-full lg:w-96" data-gsap="category">
                    @foreach(request()->except('keyword') as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $v)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <div class="relative">
                        <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="{{ __('Search for products, brands, categories...') }}"
                               class="w-full pl-11 pr-4 py-3 text-[13px] bg-white/10 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 focus:bg-white/15 transition-all backdrop-blur-sm">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ MOBILE FILTER DRAWER ═══════════════════════ --}}
    <div x-data="{ open: false }" x-cloak>
        <button @click="open = true" class="fixed bottom-6 right-6 z-50 lg:hidden px-5 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-full shadow-xl shadow-emerald-600/30 flex items-center gap-2 hover:bg-emerald-700 transition-all hover:scale-105 active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            {{ __('Filters') }}
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-50 lg:hidden" @click="open = false"></div>

        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-white z-50 shadow-2xl overflow-y-auto" @click.away="open = false">
            <div class="p-5">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-[16px] font-bold text-gray-900">{{ __('Filters') }}</h3>
                    <button @click="open = false" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="GET" action="{{ route('search') }}" class="space-y-5">
                    @if(!empty($filters['keyword']))
                        <input type="hidden" name="keyword" value="{{ $filters['keyword'] }}">
                    @endif

                    {{-- Sort --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Sort By') }}</label>
                        <select name="sort" onchange="this.form.submit()"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <option value="relevance" {{ ($filters['sort'] ?? '') === 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                            <option value="newest" {{ ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                            <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                            <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                            <option value="rating" {{ ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' }}>{{ __('Rating') }}</option>
                            <option value="popularity" {{ ($filters['sort'] ?? '') === 'popularity' ? 'selected' : '' }}>{{ __('Popularity') }}</option>
                        </select>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Category') }}</label>
                        <select name="category_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Brand') }}</label>
                        <select name="brand_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <option value="">{{ __('All Brands') }}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ ($filters['brand_id'] ?? '') == $brand->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'bn' && $brand->name_bn ? $brand->name_bn : $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Seller --}}
                    @if(isset($sellers) && $sellers->count())
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Seller') }}</label>
                            <select name="seller_id" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                                <option value="">{{ __('All Sellers') }}</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ ($filters['seller_id'] ?? '') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->store_name }} ({{ $seller->products_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Price --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Price Range') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="{{ __('Min') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <span class="text-gray-400">-</span>
                            <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="{{ __('Max') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                        </div>
                    </div>

                    {{-- Availability --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="in_stock" value="1" {{ !empty($filters['in_stock']) ? 'checked' : '' }}
                               onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label class="text-[13px] text-gray-700">{{ __('In Stock Only') }}</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_featured" value="1" {{ !empty($filters['is_featured']) ? 'checked' : '' }}
                               onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label class="text-[13px] text-gray-700">{{ __('Featured Only') }}</label>
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                        {{ __('Apply Filters') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ MAIN CONTENT ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12" x-data="{ gridCols: 3 }">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- ═══════════ DESKTOP SIDEBAR ═══════════ --}}
            <aside class="hidden lg:block w-64 flex-shrink-0">
                <div class="sticky top-24 space-y-6">
                    <form method="GET" action="{{ route('search') }}" class="space-y-5">
                        @if(!empty($filters['keyword']))
                            <input type="hidden" name="keyword" value="{{ $filters['keyword'] }}">
                        @endif

                        {{-- Sort --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Sort By') }}</label>
                            <select name="sort" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                                <option value="relevance" {{ ($filters['sort'] ?? '') === 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                                <option value="newest" {{ ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                                <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                                <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                                <option value="rating" {{ ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' }}>{{ __('Rating') }}</option>
                                <option value="popularity" {{ ($filters['sort'] ?? '') === 'popularity' ? 'selected' : '' }}>{{ __('Popularity') }}</option>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Category') }}</label>
                            <select name="category_id" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Brand --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Brand') }}</label>
                            <div class="space-y-1.5 max-h-48 overflow-y-auto">
                                @foreach($brands as $brand)
                                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                        <input type="checkbox" name="brand_id[]" value="{{ $brand->id }}"
                                               {{ in_array($brand->id, (array)($filters['brand_id'] ?? [])) ? 'checked' : '' }}
                                               onchange="this.form.submit()"
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                        <span class="text-[13px] text-gray-700 group-hover:text-gray-900">{{ app()->getLocale() === 'bn' && $brand->name_bn ? $brand->name_bn : $brand->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Price --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Price Range') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}" placeholder="{{ __('Min') }}"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}" placeholder="{{ __('Max') }}"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            </div>
                        </div>

                        {{-- Seller --}}
                        @if(isset($sellers) && $sellers->count())
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Seller') }}</label>
                                <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                    @foreach($sellers->take(10) as $seller)
                                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                            <input type="checkbox" name="seller_id[]" value="{{ $seller->id }}"
                                                   {{ in_array($seller->id, (array)($filters['seller_id'] ?? [])) ? 'checked' : '' }}
                                                   onchange="this.form.submit()"
                                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                            <span class="text-[13px] text-gray-700 group-hover:text-gray-900 flex-1 truncate">{{ $seller->store_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Availability --}}
                        <div class="space-y-2">
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Availability') }}</label>
                            <label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="in_stock" value="1"
                                       {{ !empty($filters['in_stock']) ? 'checked' : '' }}
                                       onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-[13px] text-gray-700">{{ __('In Stock Only') }}</span>
                            </label>
                            <label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="is_featured" value="1"
                                       {{ !empty($filters['is_featured']) ? 'checked' : '' }}
                                       onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-[13px] text-gray-700">{{ __('Featured Only') }}</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full px-4 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                            {{ __('Apply Filters') }}
                        </button>
                    </form>
                </div>
            </aside>

            {{-- ═══════════ PRODUCT LISTING ═══════════ --}}
            <div class="flex-1 min-w-0">

                {{-- Toolbar --}}
                <div class="flex items-center justify-between mb-6" data-gsap="section">
                    <div>
                        <h2 class="text-[18px] font-bold text-gray-900">{{ __('Products') }}</h2>
                        <p class="text-[13px] text-gray-500 mt-0.5">{{ $results->total() }} {{ __('results') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select onchange="window.location.href=this.value"
                                class="hidden sm:block rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-[13px] text-gray-700 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                            @php
                                $currentUrl = request()->url();
                                $queryParams = request()->query();
                            @endphp
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'relevance'])) }}" {{ ($filters['sort'] ?? '') === 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'newest'])) }}" {{ ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'price_asc'])) }}" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'price_desc'])) }}" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'rating'])) }}" {{ ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' }}>{{ __('Rating') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'popularity'])) }}" {{ ($filters['sort'] ?? '') === 'popularity' ? 'selected' : '' }}>{{ __('Popularity') }}</option>
                        </select>

                        <div class="hidden sm:flex items-center bg-gray-100 rounded-xl p-1">
                            <button @click="gridCols = 3" :class="gridCols === 3 ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="p-2 rounded-lg transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </button>
                            <button @click="gridCols = 2" :class="gridCols === 2 ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'" class="p-2 rounded-lg transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Active Filter Chips --}}
                @if(isset($activeFilters) && $activeFilters->count())
                    <div class="flex flex-wrap gap-2 mb-6" data-gsap="section">
                        @foreach($activeFilters as $chip)
                            @php
                                $chipParams = request()->query();
                                unset($chipParams[$chip['key']]);
                                $chipUrl = request()->url() . '?' . http_build_query($chipParams);
                            @endphp
                            <a href="{{ $chipUrl }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[12px] font-medium rounded-full hover:bg-emerald-100 transition-colors border border-emerald-100">
                                {{ $chip['label'] }}
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endforeach
                        <a href="{{ route('search') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-600 text-[12px] font-medium rounded-full hover:bg-gray-200 transition-colors">
                            {{ __('Clear All') }}
                        </a>
                    </div>
                @endif

                {{-- Product Grid --}}
                @if($results->count())
                    <div class="grid gap-4"
                         :class="gridCols === 3 ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-1 sm:grid-cols-2'">
                        @foreach($results as $product)
                            <x-storefront.product-card :product="$product" :showSeller="true" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $results->withQueryString()->links('components.storefront.pagination') }}
                    </div>
                @else
                    <div class="text-center py-20" data-gsap="section">
                        <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[18px] font-bold text-gray-900 mb-2">{{ __('No products found') }}</h3>
                        <p class="text-[14px] text-gray-500 mb-6 max-w-md mx-auto">{{ __('Try adjusting your search or filters to find what you\'re looking for.') }}</p>
                        <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                            {{ __('Clear All Filters') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ SCRIPTS ═══════════════════════ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') return;

            gsap.registerPlugin(ScrollTrigger);

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) return;

            gsap.utils.toArray('[data-gsap="section"]').forEach(el => {
                gsap.fromTo(el,
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out',
                      scrollTrigger: { trigger: el, start: 'top 88%', once: true } }
                );
            });

            gsap.utils.toArray('[data-gsap="category"]').forEach((el, i) => {
                gsap.fromTo(el,
                    { opacity: 0, y: 20 },
                    { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out', delay: i * 0.08,
                      scrollTrigger: { trigger: el, start: 'top 92%', once: true } }
                );
            });

            gsap.utils.toArray('.product-card').forEach((card, i) => {
                gsap.fromTo(card,
                    { opacity: 0, y: 30 },
                    { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out', delay: (i % 4) * 0.08,
                      scrollTrigger: { trigger: card, start: 'top 92%', once: true } }
                );

                card.addEventListener('mouseenter', () => {
                    gsap.to(card, { scale: 1.02, duration: 0.3, ease: 'power2.out' });
                });
                card.addEventListener('mouseleave', () => {
                    gsap.to(card, { scale: 1, duration: 0.3, ease: 'power2.out' });
                });
            });
        });
    </script>
    @endpush

</x-storefront.layout>
