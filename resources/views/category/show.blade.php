<x-storefront.layout title="{{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}">

    {{-- ═══════════════════════ CATEGORY BANNER ═══════════════════════ --}}
    <section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 overflow-hidden" data-gsap="section">
        @if($category->image)
            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}"
                 class="absolute inset-0 w-full h-full object-cover opacity-30">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/20 to-transparent"></div>
        <div class="relative max-w-7xl mx-auto px-4 py-12 lg:py-16">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 mb-6 text-[12px] text-gray-400" data-gsap="category">
                <a href="{{ route('home') }}" class="hover:text-emerald-400 transition-colors">{{ __('Home') }}</a>
                @foreach($breadcrumbs as $crumb)
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    @if($loop->last)
                        <span class="text-white">{{ app()->getLocale() === 'bn' && $crumb->name_bn ? $crumb->name_bn : $crumb->name }}</span>
                    @else
                        <a href="{{ route('category.show', $crumb->slug) }}" class="hover:text-emerald-400 transition-colors">{{ app()->getLocale() === 'bn' && $crumb->name_bn ? $crumb->name_bn : $crumb->name }}</a>
                    @endif
                @endforeach
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div data-gsap="category">
                    <h1 class="text-3xl lg:text-4xl font-bold text-white mb-2">{{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}</h1>
                    @if($category->description)
                        <p class="text-[14px] text-gray-400 max-w-xl leading-relaxed">{{ app()->getLocale() === 'bn' && $category->description_bn ? $category->description_bn : $category->description }}</p>
                    @endif
                    <p class="text-[13px] text-gray-500 mt-2">{{ $products->total() }} {{ __('products found') }}</p>
                </div>

                {{-- Search within category --}}
                <form method="GET" action="{{ route('category.show', $category->slug) }}" class="w-full lg:w-96" data-gsap="category">
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
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="{{ __('Search within') }} {{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}..."
                               class="w-full pl-11 pr-4 py-3 text-[13px] bg-white/10 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 focus:bg-white/15 transition-all backdrop-blur-sm">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </form>
            </div>

            {{-- Subcategories --}}
            @if($children->count())
                <div class="flex items-center gap-2 mt-8 overflow-x-auto pb-2 scrollbar-none" data-gsap="category">
                    @foreach($children as $child)
                        <a href="{{ route('category.show', $child->slug) }}"
                           class="flex-shrink-0 px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-[12px] font-medium rounded-full transition-all backdrop-blur-sm border border-white/10 hover:border-white/20">
                            {{ app()->getLocale() === 'bn' && $child->name_bn ? $child->name_bn : $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
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

                <form method="GET" action="{{ route('category.show', $category->slug) }}" class="space-y-5" id="mobileFilterForm">
                    @if(request('keyword'))
                        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                    @endif

                    {{-- Sort --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Sort By') }}</label>
                        <select name="sort" onchange="this.form.submit()"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <option value="relevance" {{ ($sort ?? '') === 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                            <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                            <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                            <option value="rating" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>{{ __('Rating') }}</option>
                            <option value="popularity" {{ ($sort ?? '') === 'popularity' ? 'selected' : '' }}>{{ __('Popularity') }}</option>
                        </select>
                    </div>

                    {{-- Categories --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Category') }}</label>
                        <select name="category_id" onchange="this.form.submit()"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($children as $child)
                                <option value="{{ $child->id }}" {{ (request('category_id') ?? '') == $child->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'bn' && $child->name_bn ? $child->name_bn : $child->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand --}}
                    @if($brands->count())
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Brand') }}</label>
                            <select name="brand_id" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                                <option value="">{{ __('All Brands') }}</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ (request('brand_id') ?? '') == $brand->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'bn' && $brand->name_bn ? $brand->name_bn : $brand->name }} ({{ $brand->products_count }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Seller --}}
                    @if($sellers->count())
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Seller') }}</label>
                            <select name="seller_id" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                                <option value="">{{ __('All Sellers') }}</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ (request('seller_id') ?? '') == $seller->id ? 'selected' : '' }}>
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
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ __('Min') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            <span class="text-gray-400">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ __('Max') }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                        </div>
                    </div>

                    {{-- Attributes --}}
                    @foreach($filterableAttributes as $attr)
                        @if($attr->values->count())
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'bn' && $attr->name_bn ? $attr->name_bn : $attr->name }}</label>
                                <select name="attribute_values[{{ $attr->id }}]" onchange="this.form.submit()"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($attr->values as $val)
                                        <option value="{{ $val->value }}" {{ (request('attribute_values.'.$attr->id) ?? '') === $val->value ? 'selected' : '' }}>
                                            {{ app()->getLocale() === 'bn' && $val->value_bn ? $val->value_bn : $val->value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach

                    {{-- Availability --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="in_stock" value="1" id="mobile_in_stock"
                               {{ !empty(request('in_stock')) ? 'checked' : '' }}
                               onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="mobile_in_stock" class="text-[13px] text-gray-700">{{ __('In Stock Only') }}</label>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_featured" value="1" id="mobile_featured"
                               {{ !empty(request('is_featured')) ? 'checked' : '' }}
                               onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="mobile_featured" class="text-[13px] text-gray-700">{{ __('Featured Only') }}</label>
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                        {{ __('Apply Filters') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ MAIN CONTENT ═══════════════════════ --}}
    <section class="max-w-7xl mx-auto px-4 py-8 lg:py-12" x-data="categoryListing()">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- ═══════════ DESKTOP SIDEBAR FILTERS ═══════════ --}}
            <aside class="hidden lg:block w-64 flex-shrink-0">
                <div class="sticky top-24 space-y-6">
                    <form method="GET" action="{{ route('category.show', $category->slug) }}" class="space-y-5" id="desktopFilterForm">
                        @if(request('keyword'))
                            <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                        @endif

                        {{-- Sort --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Sort By') }}</label>
                            <select name="sort" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                                <option value="relevance" {{ ($sort ?? '') === 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                                <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                                <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                                <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                                <option value="rating" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>{{ __('Rating') }}</option>
                                <option value="popularity" {{ ($sort ?? '') === 'popularity' ? 'selected' : '' }}>{{ __('Popularity') }}</option>
                            </select>
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Category') }}</label>
                            <select name="category_id" onchange="this.form.submit()"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                                <option value="">{{ __('All') }} {{ app()->getLocale() === 'bn' && $category->name_bn ? $category->name_bn : $category->name }}</option>
                                @foreach($children as $child)
                                    <option value="{{ $child->id }}" {{ (request('category_id') ?? '') == $child->id ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'bn' && $child->name_bn ? $child->name_bn : $child->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Brand --}}
                        @if($brands->count())
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Brand') }}</label>
                                <div class="space-y-1.5 max-h-48 overflow-y-auto">
                                    @foreach($brands as $brand)
                                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                            <input type="checkbox" name="brand_id[]" value="{{ $brand->id }}"
                                                   {{ in_array($brand->id, (array)(request('brand_id') ?? [])) ? 'checked' : '' }}
                                                   onchange="this.form.submit()"
                                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                            <span class="text-[13px] text-gray-700 group-hover:text-gray-900 flex-1">{{ app()->getLocale() === 'bn' && $brand->name_bn ? $brand->name_bn : $brand->name }}</span>
                                            <span class="text-[11px] text-gray-400">{{ $brand->products_count }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Price --}}
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Price Range') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ __('Min') }}"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ __('Max') }}"
                                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20">
                            </div>
                        </div>

                        {{-- Seller --}}
                        @if($sellers->count())
                            <div>
                                <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Seller') }}</label>
                                <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                    @foreach($sellers->take(10) as $seller)
                                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                            <input type="checkbox" name="seller_id[]" value="{{ $seller->id }}"
                                                   {{ in_array($seller->id, (array)(request('seller_id') ?? [])) ? 'checked' : '' }}
                                                   onchange="this.form.submit()"
                                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                            <span class="text-[13px] text-gray-700 group-hover:text-gray-900 flex-1 truncate">{{ $seller->store_name }}</span>
                                            <span class="text-[11px] text-gray-400">{{ $seller->products_count }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Attributes --}}
                        @foreach($filterableAttributes as $attr)
                            @if($attr->values->count())
                                <div>
                                    <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'bn' && $attr->name_bn ? $attr->name_bn : $attr->name }}</label>
                                    <select name="attribute_values[{{ $attr->id }}]" onchange="this.form.submit()"
                                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-[13px] text-gray-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                                        <option value="">{{ __('All') }}</option>
                                        @foreach($attr->values as $val)
                                            <option value="{{ $val->value }}" {{ (request('attribute_values.'.$attr->id) ?? '') === $val->value ? 'selected' : '' }}>
                                                {{ app()->getLocale() === 'bn' && $val->value_bn ? $val->value_bn : $val->value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach

                        {{-- Availability --}}
                        <div class="space-y-2">
                            <label class="block text-[12px] font-semibold text-gray-900 uppercase tracking-wider mb-2">{{ __('Availability') }}</label>
                            <label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="in_stock" value="1"
                                       {{ !empty(request('in_stock')) ? 'checked' : '' }}
                                       onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="text-[13px] text-gray-700">{{ __('In Stock Only') }}</span>
                            </label>
                            <label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="is_featured" value="1"
                                       {{ !empty(request('is_featured')) ? 'checked' : '' }}
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

            {{-- ═══════════ PRODUCT LISTING AREA ═══════════ --}}
            <div class="flex-1 min-w-0">

                {{-- Toolbar --}}
                <div class="flex items-center justify-between mb-6" data-gsap="section">
                    <div>
                        <h2 class="text-[18px] font-bold text-gray-900">{{ __('Products') }}</h2>
                        <p class="text-[13px] text-gray-500 mt-0.5">{{ $products->total() }} {{ __('results') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Sort dropdown --}}
                        <select onchange="window.location.href=this.value"
                                class="hidden sm:block rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-[13px] text-gray-700 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-50/20 transition-colors">
                            @php
                                $currentUrl = request()->url();
                                $queryParams = request()->query();
                            @endphp
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'relevance'])) }}" {{ ($sort ?? '') === 'relevance' ? 'selected' : '' }}>{{ __('Relevance') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'newest'])) }}" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'price_asc'])) }}" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'price_desc'])) }}" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'rating'])) }}" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>{{ __('Rating') }}</option>
                            <option value="{{ $currentUrl . '?' . http_build_query(array_merge($queryParams, ['sort' => 'popularity'])) }}" {{ ($sort ?? '') === 'popularity' ? 'selected' : '' }}>{{ __('Popularity') }}</option>
                        </select>

                        {{-- Grid toggle --}}
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
                @if($activeFilters->count())
                    <div class="flex flex-wrap gap-2 mb-6" data-gsap="section">
                        @foreach($activeFilters as $chip)
                            @php
                                $chipUrl = request()->fullUrlWithQuery([$chip['key'] => null]);
                            @endphp
                            <a href="{{ $chipUrl }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[12px] font-medium rounded-full hover:bg-emerald-100 transition-colors border border-emerald-100">
                                {{ $chip['label'] }}
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endforeach
                        <a href="{{ route('category.show', $category->slug) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-600 text-[12px] font-medium rounded-full hover:bg-gray-200 transition-colors">
                            {{ __('Clear All') }}
                        </a>
                    </div>
                @endif

                {{-- Product Grid --}}
                @if($products->count())
                    <div class="grid gap-4"
                         :class="gridCols === 3 ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-1 sm:grid-cols-2'"
                         id="productGrid">
                        @foreach($products as $product)
                            <x-storefront.product-card :product="$product" :showSeller="true" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-10">
                        {{ $products->withQueryString()->links('components.storefront.pagination') }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-20" data-gsap="section">
                        <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-[18px] font-bold text-gray-900 mb-2">{{ __('No products found') }}</h3>
                        <p class="text-[14px] text-gray-500 mb-6 max-w-md mx-auto">{{ __('Try adjusting your filters or search terms to find what you\'re looking for.') }}</p>
                        <a href="{{ route('category.show', $category->slug) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20">
                            {{ __('Clear All Filters') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ QUICK VIEW MODAL ═══════════════════════ --}}
    <div x-data="quickView()" x-cloak>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click="close()">
            <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.stop>
                <template x-if="product">
                    <div>
                        {{-- Header --}}
                        <div class="flex items-center justify-between p-5 border-b border-gray-100">
                            <h3 class="text-[16px] font-bold text-gray-900 truncate pr-4" x-text="product.name"></h3>
                            <button @click="close()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Image --}}
                            <div class="aspect-square bg-gray-50 rounded-xl overflow-hidden">
                                <img :src="product.image" :alt="product.name" class="w-full h-full object-cover" loading="lazy">
                            </div>

                            {{-- Info --}}
                            <div class="space-y-4">
                                <div>
                                    <p x-show="product.brand" class="text-[11px] font-semibold text-indigo-500 uppercase tracking-wider mb-1" x-text="product.brand"></p>
                                    <h2 class="text-[18px] font-bold text-gray-900" x-text="product.name"></h2>
                                </div>

                                {{-- Rating --}}
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-0.5">
                                        <template x-for="i in 5">
                                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        </template>
                                    </div>
                                    <span class="text-[12px] text-gray-400">(4.0)</span>
                                </div>

                                {{-- Price --}}
                                <div class="flex items-baseline gap-3">
                                    <span class="text-[24px] font-bold text-gray-900" x-text="'$' + parseFloat(product.price).toFixed(2)"></span>
                                    <span x-show="product.compare_at_price && product.compare_at_price > product.price" class="text-[14px] text-gray-400 line-through" x-text="product.compare_at_price ? '$' + parseFloat(product.compare_at_price).toFixed(2) : ''"></span>
                                    <span x-show="product.discount" class="px-2 py-0.5 bg-red-50 text-red-600 text-[11px] font-bold rounded-full" x-text="'-' + product.discount + '%'"></span>
                                </div>

                                {{-- Seller --}}
                                <div x-show="product.seller" class="flex items-center gap-2 text-[13px] text-gray-500">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span x-text="product.seller"></span>
                                </div>

                                {{-- Stock --}}
                                <div class="flex items-center gap-2">
                                    <span x-show="product.in_stock" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-[12px] font-semibold rounded-full">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        {{ __('In Stock') }}
                                    </span>
                                    <span x-show="!product.in_stock" class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-600 text-[12px] font-semibold rounded-full">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                        {{ __('Sold Out') }}
                                    </span>
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-3 pt-2">
                                    <button class="flex-1 py-3 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                        {{ __('Add to Cart') }}
                                    </button>
                                    <a :href="product.url" class="px-5 py-3 bg-gray-100 text-gray-700 text-[13px] font-semibold rounded-xl hover:bg-gray-200 transition-colors flex items-center gap-2">
                                        {{ __('View Details') }}
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Loading skeleton --}}
                <template x-if="loading">
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="aspect-square shimmer rounded-xl"></div>
                            <div class="space-y-4">
                                <div class="h-4 shimmer rounded w-1/3"></div>
                                <div class="h-6 shimmer rounded w-2/3"></div>
                                <div class="h-4 shimmer rounded w-1/2"></div>
                                <div class="h-8 shimmer rounded w-1/4"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ SCRIPTS ═══════════════════════ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script>
        function categoryListing() {
            return {
                gridCols: 3,
            };
        }

        function quickView() {
            return {
                open: false,
                loading: false,
                product: null,
                async show(slug) {
                    this.open = true;
                    this.loading = true;
                    this.product = null;
                    try {
                        const resp = await fetch(`/product/${slug}`, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' } });
                        const html = await resp.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const nameEl = doc.querySelector('h1');
                        const priceEl = doc.querySelector('[class*="text-2xl"], [class*="text-[22px]"]');
                        const imgEl = doc.querySelector('img');
                        this.product = {
                            name: nameEl?.textContent?.trim() || '',
                            price: priceEl?.textContent?.replace('$', '').trim() || '0',
                            image: imgEl?.src || '',
                            brand: '',
                            seller: '',
                            in_stock: true,
                            discount: null,
                            compare_at_price: null,
                            url: `/product/${slug}`,
                        };
                    } catch (e) {
                        this.product = { name: 'Product', price: '0', image: '', brand: '', seller: '', in_stock: true, discount: null, compare_at_price: null, url: `/product/${slug}` };
                    }
                    this.loading = false;
                },
                close() {
                    this.open = false;
                    this.product = null;
                }
            };
        }

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
