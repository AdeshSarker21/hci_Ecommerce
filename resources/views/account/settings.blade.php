<x-account.layout :title="__('Account Settings')" active="settings">

    <div class="max-w-2xl space-y-6">

        {{-- Header --}}
        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('Account Settings') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('Manage your language and timezone preferences.') }}</p>
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

        {{-- Settings Form --}}
        <form method="POST" action="{{ route('account.settings.update') }}" class="bg-white rounded-2xl border border-gray-100 overflow-hidden account-card" style="opacity: 0;">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-5">
                {{-- Language --}}
                <div>
                    <label for="locale" class="text-[13px] font-semibold text-gray-900 block mb-3">{{ __('Language') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="locale" value="en" {{ old('locale', $user->locale ?? 'en') === 'en' ? 'checked' : '' }} class="peer sr-only">
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all hover:border-gray-300">
                                <p class="text-[14px] font-semibold text-gray-900">English</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">EN</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="locale" value="bn" {{ old('locale', $user->locale ?? 'en') === 'bn' ? 'checked' : '' }} class="peer sr-only">
                            <div class="p-4 border-2 border-gray-200 rounded-xl text-center peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all hover:border-gray-300">
                                <p class="text-[14px] font-semibold text-gray-900">&#2476;&#2494;&#2480;&#2467; &#2474;&#2497;&#2480;&#2481;&#2503;</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">BN</p>
                            </div>
                        </label>
                    </div>
                    @error('locale')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Timezone --}}
                <div>
                    <label for="timezone" class="text-[13px] font-semibold text-gray-900 block mb-1.5">{{ __('Timezone') }}</label>
                    <select id="timezone" name="timezone" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-[13px] text-gray-900 focus:outline-none focus:bg-white focus:border-emerald-300 focus:ring-2 focus:ring-emerald-500/10 transition-all">
                        <option value="UTC" {{ old('timezone', $user->timezone ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="Asia/Dhaka" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (GMT+6)</option>
                        <option value="Asia/Kolkata" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (GMT+5:30)</option>
                        <option value="Asia/Shanghai" {{ old('timezone', $user->timezone ?? 'UTC') === 'Asia/Shanghai' ? 'selected' : '' }}>Asia/Shanghai (GMT+8)</option>
                        <option value="America/New_York" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/New_York' ? 'selected' : '' }}>America/New_York (GMT-5)</option>
                        <option value="America/Los_Angeles" {{ old('timezone', $user->timezone ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (GMT-8)</option>
                        <option value="Europe/London" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT+0)</option>
                        <option value="Europe/Paris" {{ old('timezone', $user->timezone ?? 'UTC') === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (GMT+1)</option>
                        <option value="Australia/Sydney" {{ old('timezone', $user->timezone ?? 'UTC') === 'Australia/Sydney' ? 'selected' : '' }}>Australia/Sydney (GMT+10)</option>
                    </select>
                    @error('timezone')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm">
                    {{ __('Save Settings') }}
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
