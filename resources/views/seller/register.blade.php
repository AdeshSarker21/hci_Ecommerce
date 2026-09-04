<x-seller.layout title="Register as Seller" active="register">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">Become a Seller</h2>
            <p class="text-sm text-gray-500 mb-6">Fill out the form below to apply as a seller on our marketplace.</p>

            <form method="POST" action="{{ route('seller.register.store') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700">Store Name *</label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}" required
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('store_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="store_description" class="block text-sm font-medium text-gray-700">Store Description</label>
                        <textarea name="store_description" id="store_description" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('store_description') }}</textarea>
                        @error('store_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('contact_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="contact_website" class="block text-sm font-medium text-gray-700">Website</label>
                            <input type="url" name="contact_website" id="contact_website" value="{{ old('contact_website') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('contact_website') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="business_address" class="block text-sm font-medium text-gray-700">Business Address</label>
                        <input type="text" name="business_address" id="business_address" value="{{ old('business_address') }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('business_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="business_city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="business_city" id="business_city" value="{{ old('business_city') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('business_city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="business_state" class="block text-sm font-medium text-gray-700">State</label>
                            <input type="text" name="business_state" id="business_state" value="{{ old('business_state') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('business_state') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="business_country" class="block text-sm font-medium text-gray-700">Country</label>
                            <input type="text" name="business_country" id="business_country" value="{{ old('business_country') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('business_country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="business_registration_number" class="block text-sm font-medium text-gray-700">Registration Number</label>
                            <input type="text" name="business_registration_number" id="business_registration_number" value="{{ old('business_registration_number') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('business_registration_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tax_id" class="block text-sm font-medium text-gray-700">Tax ID</label>
                            <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('tax_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end space-x-3">
                    <a href="{{ route('seller.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</x-seller.layout>
