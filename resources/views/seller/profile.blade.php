<x-seller.layout title="Store Profile" active="profile">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Edit Store Profile</h2>

            <form method="POST" action="{{ route('seller.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700">Store Name</label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $seller->store_name) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('store_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="store_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="store_description" id="store_description" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('store_description', $seller->store_description) }}</textarea>
                        @error('store_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $seller->contact_phone) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label for="contact_website" class="block text-sm font-medium text-gray-700">Website</label>
                            <input type="url" name="contact_website" id="contact_website" value="{{ old('contact_website', $seller->contact_website) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="business_address" class="block text-sm font-medium text-gray-700">Business Address</label>
                        <input type="text" name="business_address" id="business_address" value="{{ old('business_address', $seller->business_address) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="business_city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="business_city" id="business_city" value="{{ old('business_city', $seller->business_city) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label for="business_state" class="block text-sm font-medium text-gray-700">State</label>
                            <input type="text" name="business_state" id="business_state" value="{{ old('business_state', $seller->business_state) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label for="business_country" class="block text-sm font-medium text-gray-700">Country</label>
                            <input type="text" name="business_country" id="business_country" value="{{ old('business_country', $seller->business_country) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('seller.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-seller.layout>
