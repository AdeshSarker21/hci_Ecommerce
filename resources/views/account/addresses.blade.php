<x-account.layout :title="__('Addresses')" active="addresses">

    <div class="space-y-6" x-data="addressManager()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 account-header" style="opacity: 0;">
            <div>
                <h1 class="text-[22px] font-bold text-gray-900">{{ __('My Addresses') }}</h1>
                <p class="text-[13px] text-gray-500 mt-1">{{ __('Manage your shipping and billing addresses.') }}</p>
            </div>
            <button @click="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add Address') }}
            </button>
        </div>

        {{-- Address Grid --}}
        @if($addresses->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No addresses saved') }}</h3>
                <p class="text-[13px] text-gray-500 mb-5">{{ __('Add a shipping address for faster checkout.') }}</p>
                <button @click="openModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Add Address') }}
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 account-grid" style="opacity: 0;">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 relative address-card" id="address-{{ $address->id }}" data-gsap="address">
                        @if($address->is_default)
                            <span class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">{{ __('Default') }}</span>
                        @endif
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold text-gray-900">{{ $address->label ? ucfirst($address->label) : __('Address') }}</p>
                                <p class="text-[13px] font-medium text-gray-700">{{ $address->name }}</p>
                            </div>
                        </div>
                        <div class="text-[12px] text-gray-500 space-y-0.5 mb-4">
                            <p>{{ $address->address_line_1 }}</p>
                            @if($address->address_line_2)
                                <p>{{ $address->address_line_2 }}</p>
                            @endif
                            <p>{{ $address->city }}@if($address->state), {{ $address->state }}@endif @if($address->postal_code), {{ $address->postal_code }}@endif</p>
                            <p>{{ $address->country }}</p>
                            @if($address->phone)
                                <p class="mt-1">{{ $address->phone }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                            <button @click="editAddress({{ $address->toJson() }})" class="text-[11px] font-medium text-emerald-600 hover:text-emerald-700 transition-colors">{{ __('Edit') }}</button>
                            @if(!$address->is_default)
                                <button @click="setDefault({{ $address->id }})" class="text-[11px] font-medium text-gray-500 hover:text-emerald-600 transition-colors">{{ __('Set Default') }}</button>
                            @endif
                            <button @click="deleteAddress({{ $address->id }})" class="text-[11px] font-medium text-red-500 hover:text-red-600 transition-colors ml-auto">{{ __('Delete') }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Address Modal --}}
        <div x-show="showModal" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
            <div @click.away="showModal = false"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-[15px] font-bold text-gray-900" x-text="editingId ? '{{ __("Edit Address") }}' : '{{ __("Add New Address") }}'"></h2>
                    <button @click="showModal = false" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="saveAddress()" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Label') }}</label>
                            <select x-model="form.label" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                                <option value="home">{{ __('Home') }}</option>
                                <option value="office">{{ __('Office') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Full Name') }}</label>
                            <input type="text" x-model="form.name" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Phone') }}</label>
                            <input type="text" x-model="form.phone" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                        </div>
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Email') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                            <input type="email" x-model="form.email" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Address Line 1') }}</label>
                        <input type="text" x-model="form.address_line_1" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Address Line 2') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                        <input type="text" x-model="form.address_line_2" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('City') }}</label>
                            <input type="text" x-model="form.city" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                        </div>
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('State') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                            <input type="text" x-model="form.state" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                        </div>
                        <div>
                            <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Postal Code') }}</label>
                            <input type="text" x-model="form.postal_code" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Country') }}</label>
                        <input type="text" x-model="form.country" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] focus:outline-none focus:bg-white focus:border-emerald-300 transition-all">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="form.is_default" id="is_default" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="is_default" class="text-[12px] text-gray-700">{{ __('Set as default address') }}</label>
                    </div>

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                            <template x-for="error in Object.values($errors)" :key="error">
                                <p class="text-[11px] text-red-500" x-text="error"></p>
                            </template>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2.5 text-[13px] font-medium text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">{{ __('Cancel') }}</button>
                        <button type="submit" :disabled="saving" class="px-6 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-50">
                            <span x-show="!saving" x-text="editingId ? '{{ __("Update") }}' : '{{ __("Save") }}'"></span>
                            <span x-show="saving">{{ __('Saving...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        function addressManager() {
            return {
                showModal: false,
                editingId: null,
                saving: false,
                form: {
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
                openModal() {
                    this.editingId = null;
                    this.form = { label: 'home', name: '', phone: '', email: '', address_line_1: '', address_line_2: '', city: '', state: '', postal_code: '', country: 'Bangladesh', is_default: false };
                    this.showModal = true;
                },
                editAddress(address) {
                    this.editingId = address.id;
                    this.form = { ...address };
                    this.showModal = true;
                },
                async saveAddress() {
                    this.saving = true;
                    const url = this.editingId ? `/account/addresses/${this.editingId}` : '/account/addresses';
                    const method = this.editingId ? 'PUT' : 'POST';
                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.showModal = false;
                            location.reload();
                        } else {
                            alert(data.message || '{{ __("Something went wrong.") }}');
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.saving = false;
                    }
                },
                async deleteAddress(id) {
                    if (!confirm('{{ __("Are you sure you want to delete this address?") }}')) return;
                    try {
                        const res = await fetch(`/account/addresses/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const card = document.getElementById('address-' + id);
                            if (card && typeof gsap !== 'undefined') {
                                await gsap.to(card, { opacity: 0, scale: 0.9, duration: 0.3, ease: 'power2.in' });
                            }
                            location.reload();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },
                async setDefault(id) {
                    try {
                        const res = await fetch(`/account/addresses/${id}/default`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) location.reload();
                    } catch (e) {
                        console.error(e);
                    }
                },
                init() {
                    this.$nextTick(() => {
                        if (typeof gsap === 'undefined') {
                            document.querySelectorAll('.account-header, .account-empty, .account-grid, .address-card').forEach(el => el.style.opacity = '1');
                            return;
                        }
                        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
                        gsap.set(['.account-header', '.account-empty', '.account-grid'], { y: 20 });
                        tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
                          .to(['.account-empty', '.account-grid'], { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
                          .to('.address-card', { opacity: 1, y: 0, duration: 0.3, stagger: 0.08 }, '-=0.3');
                    });
                }
            };
        }
    </script>
    @endpush
</x-account.layout>
