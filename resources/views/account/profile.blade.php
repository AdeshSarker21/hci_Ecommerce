<x-account.layout :title="__('Profile')" active="profile">
    @php $locale = app()->getLocale(); @endphp

    <div class="max-w-2xl space-y-6">

        {{-- Header --}}
        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('Profile Settings') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('Update your personal information and profile picture.') }}</p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-[13px] font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Profile Form --}}
        <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-gray-100 overflow-hidden account-card" style="opacity: 0;">
            @csrf
            @method('PUT')

            {{-- Avatar Section --}}
            <div class="p-6 border-b border-gray-100">
                <label class="text-[13px] font-semibold text-gray-900 block mb-3">{{ __('Profile Picture') }}</label>
                <div class="flex items-center gap-4" x-data="{ avatarPreview: '{{ $user->avatar_url }}' }">
                    <img :src="avatarPreview" alt="{{ $user->name }}" class="w-20 h-20 rounded-2xl object-cover ring-2 ring-gray-100">
                    <div>
                        <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-[12px] font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ __('Change Photo') }}
                            <input type="file" name="avatar" class="hidden" accept="image/*" x-on:change="avatarPreview = URL.createObjectURL($event.target.files[0])">
                        </label>
                        <p class="text-[11px] text-gray-400 mt-1.5">{{ __('JPEG, PNG, JPG, GIF or WebP. Max 2MB.') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-5">
                {{-- Name --}}
                <div>
                    <label for="name" class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Full Name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:bg-white focus:border-emerald-300 focus:ring-2 focus:ring-emerald-500/10 transition-all @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Email Address') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:bg-white focus:border-emerald-300 focus:ring-2 focus:ring-emerald-500/10 transition-all @error('email') border-red-300 @enderror">
                    @error('email')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="text-[12px] font-semibold text-gray-700 block mb-1.5">{{ __('Phone Number') }}</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:bg-white focus:border-emerald-300 focus:ring-2 focus:ring-emerald-500/10 transition-all @error('phone') border-red-300 @enderror">
                    @error('phone')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.account-header, .account-card').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.account-header', '.account-card'], { y: 20 });
            tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
              .to('.account-card', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3');
        });
    </script>
    @endpush
</x-account.layout>
