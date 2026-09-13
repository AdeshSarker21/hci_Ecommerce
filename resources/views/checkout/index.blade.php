<x-storefront.layout title="{{ __('Checkout') }}">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <nav class="flex items-center gap-2 text-[12px] text-gray-400">
                <a href="/" class="hover:text-emerald-600 transition-colors">{{ __('Home') }}</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('cart.index') }}" class="hover:text-emerald-600 transition-colors">{{ __('Cart') }}</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-medium">{{ __('Checkout') }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8" x-data="checkoutApp()" x-init="init()">

        {{-- Page Title --}}
        <div class="mb-8" data-gsap="checkout-section">
            <h1 class="text-[28px] font-bold text-gray-900">{{ __('Checkout') }}</h1>
            <p class="text-[13px] text-gray-400 mt-1">{{ __('Review your order and complete your purchase') }}</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Left Column --}}
            <div class="flex-1 space-y-6">

                {{-- Customer Information --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6" data-gsap="checkout-section">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[16px] font-bold text-gray-900">{{ __('Customer Information') }}</h2>
                            <p class="text-[11px] text-gray-400">{{ __('Your account details') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[12px] font-medium text-gray-600 mb-1.5">{{ __('Full Name') }}</label>
                            <input type="text" x-model="form.name"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-gray-600 mb-1.5">{{ __('Phone') }}</label>
                            <input type="text" x-model="form.phone"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-[12px] font-medium text-gray-600 mb-1.5">{{ __('Email') }}</label>
                            <input type="email" x-model="form.email"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6" data-gsap="checkout-section">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-[16px] font-bold text-gray-900">{{ __('Shipping Address') }}</h2>
                                <p class="text-[11px] text-gray-400">{{ __('Where should we deliver?') }}</p>
                            </div>
                        </div>
                        <button @click="showAddressForm = !showAddressForm; editingAddress = null; resetAddressForm()"
                                class="text-[12px] font-medium text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('Add Address') }}
                        </button>
                    </div>

                    {{-- Inline Address Form --}}
                    <div x-show="showAddressForm" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mb-5 p-5 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-[14px] font-bold text-gray-900" x-text="editingAddress ? '{{ __("Edit Address") }}' : '{{ __("Add New Address") }}'"></h4>
                            <button @click="showAddressForm = false; editingAddress = null"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Label') }}</label>
                                <select x-model="addressForm.label" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="home">{{ __('Home') }}</option>
                                    <option value="office">{{ __('Office') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Full Name') }} *</label>
                                    <input type="text" x-model="addressForm.name" required
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Phone') }} *</label>
                                    <input type="text" x-model="addressForm.phone" required
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Email') }}</label>
                                <input type="email" x-model="addressForm.email"
                                       class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Address Line 1') }} *</label>
                                <input type="text" x-model="addressForm.address_line_1" required
                                       class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Address Line 2') }}</label>
                                <input type="text" x-model="addressForm.address_line_2"
                                       class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('City') }} *</label>
                                    <input type="text" x-model="addressForm.city" required
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('State') }}</label>
                                    <input type="text" x-model="addressForm.state"
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Postal Code') }}</label>
                                    <input type="text" x-model="addressForm.postal_code"
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-medium text-gray-600 mb-1">{{ __('Country') }} *</label>
                                    <input type="text" x-model="addressForm.country" required
                                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="addressForm.is_default" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-[12px] text-gray-600">{{ __('Set as default address') }}</span>
                            </label>
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button @click="showAddressForm = false; editingAddress = null"
                                    class="flex-1 py-2.5 bg-gray-200 text-gray-700 text-[13px] font-semibold rounded-xl hover:bg-gray-300 transition-colors">
                                {{ __('Cancel') }}
                            </button>
                            <button @click="saveAddress()"
                                    :disabled="addressSaving"
                                    class="flex-1 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2">
                                <template x-if="addressSaving">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="editingAddress ? '{{ __("Update Address") }}' : '{{ __("Save Address") }}'"></span>
                            </button>
                        </div>
                    </div>

                    @if($addresses->isEmpty())
                        <div class="text-center py-8 bg-gray-50 rounded-xl">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            <p class="text-[13px] text-gray-500 mb-3">{{ __('No addresses saved yet') }}</p>
                            <button @click="showAddressForm = true; editingAddress = null; resetAddressForm()"
                                    class="px-5 py-2 bg-emerald-600 text-white text-[12px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                                {{ __('Add Your First Address') }}
                            </button>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($addresses as $address)
                                <div @click="form.address_id = {{ $address->id }}"
                                     class="relative p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
                                     :class="form.address_id == {{ $address->id }} ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                     data-gsap="checkout-section">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-3">
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center mt-0.5 flex-shrink-0 transition-all"
                                                 :class="form.address_id == {{ $address->id }} ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300'">
                                                <template x-if="form.address_id == {{ $address->id }}">
                                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[13px] font-semibold text-gray-900">{{ $address->name }}</span>
                                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 uppercase">{{ $address->label }}</span>
                                                    @if($address->is_default)
                                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600">{{ __('Default') }}</span>
                                                    @endif
                                                </div>
                                                <p class="text-[12px] text-gray-500 mt-1">{{ $address->phone }}</p>
                                                <p class="text-[12px] text-gray-600 mt-0.5">{{ $address->address_line_1 }}{{ $address->address_line_2 ? ', ' . $address->address_line_2 : '' }}</p>
                                                <p class="text-[12px] text-gray-600">{{ $address->city }}{{ $address->state ? ', ' . $address->state : '' }}{{ $address->postal_code ? ' ' . $address->postal_code : '' }}</p>
                                                <p class="text-[12px] text-gray-500">{{ $address->country }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1" @click.stop>
                                            <button @click="editAddress({{ json_encode($address->toArray()) }})"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button @click="deleteAddress({{ $address->id }})"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition-all">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <template x-if="errors.address_id">
                        <p class="text-[12px] text-red-500 mt-2" x-text="errors.address_id[0]"></p>
                    </template>
                </div>

                {{-- Order Items by Seller --}}
                <div data-gsap="checkout-section">
                    <h2 class="text-[16px] font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        {{ __('Order Items') }}
                        <span class="text-[12px] font-normal text-gray-400" x-text="'({{ $cart_items->count() }} {{ __('items') }})'"></span>
                    </h2>

                    @php $locale = app()->getLocale(); @endphp

                    @foreach($items_by_seller as $sellerGroup)
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4">
                            {{-- Seller Header --}}
                            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center overflow-hidden">
                                        @if($sellerGroup['seller']->store_logo)
                                            <img src="{{ asset('storage/' . $sellerGroup['seller']->store_logo) }}" alt="{{ $sellerGroup['seller']->store_name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-[11px] font-bold text-emerald-600">{{ strtoupper(substr($sellerGroup['seller']->store_name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-900">{{ $sellerGroup['seller']->store_name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $sellerGroup['item_count'] }} {{ __('item(s)') }}</p>
                                    </div>
                                </div>
                                @if($sellerGroup['shipping'] > 0)
                                    <span class="text-[11px] text-gray-500">{{ __('Shipping') }}: ${{ number_format($sellerGroup['shipping'], 2) }}</span>
                                @else
                                    <span class="text-[11px] text-emerald-600 font-medium">{{ __('Free Shipping') }}</span>
                                @endif
                            </div>

                            {{-- Items --}}
                            <div class="divide-y divide-gray-50">
                                @foreach($sellerGroup['items'] as $item)
                                    @php
                                        $product = $item->product;
                                        $productName = $locale === 'bn' && $product->name_bn ? $product->name_bn : $product->name;
                                        $img = $product->primary_image ? asset('storage/' . $product->primary_image) : null;
                                        $variant = $item->selected_variants ? implode(', ', array_map(function($v) { return is_array($v) ? $v['value'] ?? '' : $v; }, $item->selected_variants)) : null;
                                    @endphp
                                    <div class="px-5 py-4 flex gap-4">
                                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0 border border-gray-100">
                                            @if($img)
                                                <img src="{{ $img }}" alt="{{ $productName }}" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-[13px] font-semibold text-gray-900 line-clamp-2">{{ $productName }}</h3>
                                            @if($variant)
                                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $variant }}</p>
                                            @endif
                                            <div class="flex items-center justify-between mt-2">
                                                <span class="text-[12px] text-gray-500">{{ __('Qty') }}: {{ $item->quantity }}</span>
                                                <div class="text-right">
                                                    <span class="text-[14px] font-bold text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</span>
                                                    @if($item->quantity > 1)
                                                        <p class="text-[10px] text-gray-400">${{ number_format($item->price, 2) }} {{ __('each') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Order Notes --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6" data-gsap="checkout-section">
                    <label class="block text-[13px] font-medium text-gray-700 mb-2">{{ __('Order Notes') }} <span class="text-gray-400">({{ __('Optional') }})</span></label>
                    <textarea x-model="form.notes" rows="2"
                              placeholder="{{ __('Any special instructions for your order?') }}"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all resize-none"></textarea>
                </div>
            </div>

            {{-- Right Column: Order Summary --}}
            <div class="w-full lg:w-[380px] flex-shrink-0">
                <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24 space-y-5" data-gsap="checkout-section">
                    <h2 class="text-[17px] font-bold text-gray-900">{{ __('Order Summary') }}</h2>

                    {{-- Coupon --}}
                    <div class="space-y-2">
                        <label class="text-[12px] font-medium text-gray-600">{{ __('Coupon Code') }}</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="couponCode"
                                   :disabled="couponApplied"
                                   placeholder="{{ __('Enter code') }}"
                                   class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-[12px] text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 disabled:opacity-50">
                            @if(!$coupon_code)
                                <button @click="applyCoupon()"
                                        :disabled="loading || !couponCode.trim()"
                                        class="px-4 py-2 bg-gray-900 text-white text-[11px] font-semibold rounded-xl hover:bg-gray-800 transition-colors disabled:opacity-50">
                                    {{ __('Apply') }}
                                </button>
                            @else
                                <button @click="removeCoupon()"
                                        class="px-4 py-2 bg-red-50 text-red-600 text-[11px] font-semibold rounded-xl hover:bg-red-100 transition-colors">
                                    {{ __('Remove') }}
                                </button>
                            @endif
                        </div>
                        <template x-if="couponError">
                            <p class="text-[11px] text-red-500" x-text="couponError"></p>
                        </template>
                        <template x-if="couponSuccess">
                            <p class="text-[11px] text-emerald-600" x-text="couponSuccess"></p>
                        </template>
                    </div>

                    {{-- Summary Lines --}}
                    <div class="space-y-3 pt-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[13px] text-gray-500">{{ __('Subtotal') }}</span>
                            <span class="text-[13px] font-semibold text-gray-900">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($coupon_discount > 0)
                            <div class="flex items-center justify-between">
                                <span class="text-[13px] text-gray-500">{{ __('Discount') }}</span>
                                <span class="text-[13px] font-semibold text-emerald-600">-${{ number_format($coupon_discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-[13px] text-gray-500">{{ __('Shipping') }}</span>
                            @if($shipping > 0)
                                <span class="text-[13px] font-semibold text-gray-900">${{ number_format($shipping, 2) }}</span>
                            @else
                                <span class="text-[13px] font-semibold text-emerald-600">{{ __('Free') }}</span>
                            @endif
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex items-center justify-between">
                            <span class="text-[15px] font-bold text-gray-900">{{ __('Total') }}</span>
                            <span class="text-[20px] font-bold text-gray-900">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="space-y-3">
                        <label class="text-[12px] font-medium text-gray-600">{{ __('Payment Method') }}</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="form.payment_method === 'cod' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" x-model="form.payment_method" value="cod" class="hidden">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                     :class="form.payment_method === 'cod' ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300'">
                                    <template x-if="form.payment_method === 'cod'">
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-900">{{ __('Cash on Delivery') }}</p>
                                        <p class="text-[10px] text-gray-400">{{ __('Pay when you receive') }}</p>
                                    </div>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="form.payment_method === 'online' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" x-model="form.payment_method" value="online" class="hidden">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                     :class="form.payment_method === 'online' ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300'">
                                    <template x-if="form.payment_method === 'online'">
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <div>
                                        <p class="text-[13px] font-semibold text-gray-900">{{ __('Online Payment') }}</p>
                                        <p class="text-[10px] text-gray-400">{{ __('Card, Mobile Banking') }}</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <template x-if="errors.payment_method">
                            <p class="text-[12px] text-red-500" x-text="errors.payment_method[0]"></p>
                        </template>
                    </div>

                    {{-- Place Order --}}
                    <button @click="placeOrder()"
                            :disabled="loading || !form.address_id || !form.payment_method"
                            class="w-full py-3.5 bg-emerald-600 text-white text-[14px] font-semibold rounded-xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-lg flex items-center justify-center gap-2">
                        <template x-if="loading">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!loading">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </template>
                        <span x-text="loading ? '{{ __('Processing...') }}' : '{{ __('Place Order') }} — $' + {{ number_format($total, 2) }}"></span>
                    </button>

                    {{-- Errors --}}
                    <template x-if="orderError">
                        <div class="p-3 bg-red-50 border border-red-200 rounded-xl">
                            <p class="text-[12px] text-red-600" x-text="orderError"></p>
                        </div>
                    </template>

                    {{-- Trust --}}
                    <div class="flex items-center justify-center gap-4 pt-1">
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            {{ __('Secure') }}
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            {{ __('Encrypted') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Modal --}}
        <template x-if="orderSuccess">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-gray-900 mb-2">{{ __('Order Placed!') }}</h3>
                    <p class="text-[13px] text-gray-500 mb-6">{{ __('Your order has been placed successfully. You will receive a confirmation shortly.') }}</p>
                    <div class="space-y-3">
                        <a :href="orderConfirmationUrl" class="block w-full py-3 bg-emerald-600 text-white text-[14px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                            {{ __('View Order Confirmation') }}
                        </a>
                        <a href="/" class="block w-full py-3 bg-white text-gray-700 text-[14px] font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors">
                            {{ __('Continue Shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </template>

    </div>

    @push('scripts')
    <script>
        function checkoutApp() {
            return {
                form: {
                    name: '{{ addslashes($user->name ?? '') }}',
                    phone: '{{ addslashes($user->phone ?? '') }}',
                    email: '{{ addslashes($user->email ?? '') }}',
                    address_id: {{ $default_address ? $default_address->id : 'null' }},
                    payment_method: 'cod',
                    notes: '',
                },
                couponCode: '{{ $coupon_code ?? "" }}',
                couponApplied: {{ $coupon_discount > 0 ? 'true' : 'false' }},
                couponDiscount: {{ number_format($coupon_discount, 2, '.', '') }},
                couponError: '',
                couponSuccess: '',
                loading: false,
                errors: {},
                orderError: '',
                orderSuccess: false,
                orderConfirmationUrl: '',
                showAddressForm: false,
                editingAddress: null,
                addressSaving: false,
                addressForm: {
                    label: 'home',
                    name: '',
                    phone: '',
                    email: '',
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    postal_code: '',
                    country: 'Bangladesh',
                    is_default: false,
                },

                init() {
                    this.$nextTick(() => {
                        if (typeof gsap !== 'undefined') {
                            gsap.from('[data-gsap="checkout-section"]', {
                                opacity: 0, y: 20, duration: 0.5, stagger: 0.1, ease: 'power2.out'
                            });
                        }
                    });
                },

                resetAddressForm() {
                    this.addressForm = {
                        label: 'home',
                        name: this.form.name || '',
                        phone: this.form.phone || '',
                        email: this.form.email || '',
                        address_line_1: '',
                        address_line_2: '',
                        city: '',
                        state: '',
                        postal_code: '',
                        country: 'Bangladesh',
                        is_default: false,
                    };
                },

                editAddress(address) {
                    this.editingAddress = address.id;
                    this.addressForm = { ...address };
                    this.showAddressForm = true;
                },

                async saveAddress() {
                    this.addressSaving = true;
                    try {
                        const url = this.editingAddress
                            ? `/checkout/address/${this.editingAddress}`
                            : '/checkout/address';
                        const method = this.editingAddress ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.addressForm),
                        });

                        const data = await response.json();
                        if (data.success) {
                            const wasNew = !this.editingAddress;
                            this.showAddressForm = false;
                            this.editingAddress = null;
                            this.resetAddressForm();
                            if (wasNew) {
                                this.form.address_id = data.address.id;
                            }
                            window.location.reload();
                        } else {
                            alert(data.message || '{{ __("Failed to save address.") }}');
                        }
                    } catch (e) {
                        alert('{{ __("An error occurred. Please try again.") }}');
                    } finally {
                        this.addressSaving = false;
                    }
                },

                async deleteAddress(id) {
                    if (!confirm('{{ __("Delete this address?") }}')) return;
                    try {
                        const response = await fetch(`/checkout/address/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            if (this.form.address_id === id) this.form.address_id = null;
                            window.location.reload();
                        }
                    } catch (e) {
                        alert('{{ __("Failed to delete address.") }}');
                    }
                },

                async applyCoupon() {
                    this.couponError = '';
                    this.couponSuccess = '';
                    if (!this.couponCode.trim()) return;
                    try {
                        const response = await fetch('{{ route("cart.coupon.apply") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ code: this.couponCode }),
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.couponApplied = true;
                            this.couponDiscount = data.discount;
                            this.couponSuccess = data.message;
                            window.location.reload();
                        } else {
                            this.couponError = data.message;
                        }
                    } catch (e) {
                        this.couponError = '{{ __("Failed to apply coupon.") }}';
                    }
                },

                async removeCoupon() {
                    try {
                        await fetch('{{ route("cart.coupon.remove") }}', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });
                        this.couponCode = '';
                        this.couponApplied = false;
                        this.couponDiscount = 0;
                        this.couponSuccess = '';
                        window.location.reload();
                    } catch (e) {}
                },

                async placeOrder() {
                    if (!this.form.address_id) {
                        this.orderError = '{{ __("Please select a shipping address.") }}';
                        return;
                    }
                    if (!this.form.payment_method) {
                        this.orderError = '{{ __("Please select a payment method.") }}';
                        return;
                    }

                    this.loading = true;
                    this.orderError = '';
                    this.errors = {};

                    try {
                        const response = await fetch('{{ route("checkout.place-order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.orderSuccess = true;
                            if (data.order_ids && data.order_ids.length > 0) {
                                this.orderConfirmationUrl = '{{ url("/account/orders") }}/' + data.order_ids[0] + '/confirmation';
                            }
                            if (typeof gsap !== 'undefined') {
                                gsap.from('.fixed .bg-white', {
                                    scale: 0.9, opacity: 0, duration: 0.4, ease: 'back.out(1.7)'
                                });
                            }
                        } else {
                            this.orderError = data.message;
                            if (data.errors) {
                                data.errors.forEach(err => {
                                    this.orderError += '\n' + err.message;
                                });
                            }
                        }
                    } catch (e) {
                        this.orderError = '{{ __("An unexpected error occurred. Please try again.") }}';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
    @endpush

</x-storefront.layout>
