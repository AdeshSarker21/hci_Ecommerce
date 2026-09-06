<x-seller.layout title="Store Settings" active="profile">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Store Settings</h2>
            <p class="text-sm text-gray-500 mt-1">Manage your store profile and policies</p>
        </div>
        <a href="{{ route('storefront.show', $seller->store_slug) }}" target="_blank"
           class="inline-flex items-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            View Storefront
        </a>
    </div>

    <form method="POST" action="{{ route('seller.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            {{-- Store Identity --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Store Identity</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <div>
                            <label for="store_name" class="block text-sm font-medium text-gray-700 mb-1.5">Store Name *</label>
                            <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $seller->store_name) }}" required
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                            @error('store_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="store_tagline" class="block text-sm font-medium text-gray-700 mb-1.5">Tagline (English)</label>
                                <input type="text" name="store_tagline" id="store_tagline" value="{{ old('store_tagline', $seller->store_tagline) }}"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                       placeholder="Your store tagline">
                                @error('store_tagline') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="store_tagline_bn" class="block text-sm font-medium text-gray-700 mb-1.5">Tagline (Bangla)</label>
                                <input type="text" name="store_tagline_bn" id="store_tagline_bn" value="{{ old('store_tagline_bn', $seller->store_tagline_bn) }}"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                       placeholder="আপনার দোকানের ট্যাগলাইন">
                                @error('store_tagline_bn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="store_description" class="block text-sm font-medium text-gray-700 mb-1.5">Description (English)</label>
                            <textarea name="store_description" id="store_description" rows="3"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                      placeholder="Tell customers about your store...">{{ old('store_description', $seller->store_description) }}</textarea>
                            @error('store_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="store_description_bn" class="block text-sm font-medium text-gray-700 mb-1.5">Description (Bangla)</label>
                            <textarea name="store_description_bn" id="store_description_bn" rows="3"
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                      placeholder="আপনার দোকান সম্পর্কে গ্রাহকদের বলুন...">{{ old('store_description_bn', $seller->store_description_bn) }}</textarea>
                            @error('store_description_bn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Store Logo</label>
                            <div class="flex items-center gap-4">
                                <img src="{{ $seller->logo_url }}" alt="Current logo" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                                <div class="flex-1">
                                    <input type="file" name="store_logo" accept="image/*"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                    <p class="mt-1 text-xs text-gray-400">PNG, JPG or WebP. Max 2MB.</p>
                                </div>
                            </div>
                            @error('store_logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Store Banner</label>
                            @if($seller->store_banner)
                                <img src="{{ asset('storage/' . $seller->store_banner) }}" alt="Current banner" class="w-full h-24 rounded-lg object-cover border border-gray-200 mb-2">
                            @else
                                <div class="w-full h-24 rounded-lg bg-gradient-to-r from-emerald-100 to-teal-100 flex items-center justify-center text-emerald-500 text-sm mb-2">No banner uploaded</div>
                            @endif
                            <input type="file" name="store_banner" accept="image/*"
                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="mt-1 text-xs text-gray-400">Recommended: 1200x300px. Max 5MB.</p>
                            @error('store_banner') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Details --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1.5">Contact Email</label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $seller->contact_email) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $seller->contact_phone) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contact_website" class="block text-sm font-medium text-gray-700 mb-1.5">Website</label>
                        <input type="url" name="contact_website" id="contact_website" value="{{ old('contact_website', $seller->contact_website) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                               placeholder="https://example.com">
                        @error('contact_website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-1.5">Facebook URL</label>
                        <input type="url" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $seller->facebook_url) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                               placeholder="https://facebook.com/...">
                        @error('facebook_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-1.5">Instagram URL</label>
                        <input type="url" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $seller->instagram_url) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                               placeholder="https://instagram.com/...">
                        @error('instagram_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-1.5">YouTube URL</label>
                        <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url', $seller->youtube_url) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                               placeholder="https://youtube.com/...">
                        @error('youtube_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Business Address --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Address</h3>
                <div class="space-y-4">
                    <div>
                        <label for="business_address" class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                        <input type="text" name="business_address" id="business_address" value="{{ old('business_address', $seller->business_address) }}"
                               class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="business_city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                            <input type="text" name="business_city" id="business_city" value="{{ old('business_city', $seller->business_city) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="business_state" class="block text-sm font-medium text-gray-700 mb-1.5">State</label>
                            <input type="text" name="business_state" id="business_state" value="{{ old('business_state', $seller->business_state) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="business_country" class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                            <input type="text" name="business_country" id="business_country" value="{{ old('business_country', $seller->business_country) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                        <div>
                            <label for="business_postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">Postal Code</label>
                            <input type="text" name="business_postal_code" id="business_postal_code" value="{{ old('business_postal_code', $seller->business_postal_code) }}"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200">
                        </div>
                    </div>
                </div>
            </div>

            {{-- About Us --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">About Store</h3>
                <div class="space-y-4">
                    <div>
                        <label for="about_us" class="block text-sm font-medium text-gray-700 mb-1.5">About Us (English)</label>
                        <textarea name="about_us" id="about_us" rows="4"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                  placeholder="Tell customers about your store history, mission, and values...">{{ old('about_us', $seller->about_us) }}</textarea>
                        @error('about_us') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="about_us_bn" class="block text-sm font-medium text-gray-700 mb-1.5">About Us (Bangla)</label>
                        <textarea name="about_us_bn" id="about_us_bn" rows="4"
                                  class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                  placeholder="আপনার দোকানের ইতিহাস, লক্ষ্য এবং মূল্যবোধ সম্পর্কে গ্রাহকদের বলুন...">{{ old('about_us_bn', $seller->about_us_bn) }}</textarea>
                        @error('about_us_bn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Shipping & Return Policies --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Policies</h3>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-3">Shipping Policy</label>
                        <div class="space-y-4">
                            <div>
                                <label for="shipping_policy" class="block text-sm font-medium text-gray-700 mb-1.5">English</label>
                                <textarea name="shipping_policy" id="shipping_policy" rows="4"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                          placeholder="Describe your shipping policy...">{{ old('shipping_policy', $seller->shipping_policy) }}</textarea>
                                @error('shipping_policy') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="shipping_policy_bn" class="block text-sm font-medium text-gray-700 mb-1.5">Bangla</label>
                                <textarea name="shipping_policy_bn" id="shipping_policy_bn" rows="4"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                          placeholder="আপনার শিপিং নীতি বর্ণনা করুন...">{{ old('shipping_policy_bn', $seller->shipping_policy_bn) }}</textarea>
                                @error('shipping_policy_bn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-3">Return Policy</label>
                        <div class="space-y-4">
                            <div>
                                <label for="return_policy" class="block text-sm font-medium text-gray-700 mb-1.5">English</label>
                                <textarea name="return_policy" id="return_policy" rows="4"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                          placeholder="Describe your return policy...">{{ old('return_policy', $seller->return_policy) }}</textarea>
                                @error('return_policy') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="return_policy_bn" class="block text-sm font-medium text-gray-700 mb-1.5">Bangla</label>
                                <textarea name="return_policy_bn" id="return_policy_bn" rows="4"
                                          class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors duration-200"
                                          placeholder="আপনার রিটার্ন নীতি বর্ণনা করুন...">{{ old('return_policy_bn', $seller->return_policy_bn) }}</textarea>
                                @error('return_policy_bn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('seller.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Save All Changes
                </button>
            </div>
        </div>
    </form>
</x-seller.layout>
