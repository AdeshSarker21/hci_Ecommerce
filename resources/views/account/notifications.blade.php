<x-account.layout :title="__('Notifications')" active="notifications">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('Notifications') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('Stay updated with your order and account activity.') }}</p>
        </div>

        {{-- Notifications List --}}
        @if($notifications->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No notifications') }}</h3>
                <p class="text-[13px] text-gray-500">{{ __('You are all caught up!') }}</p>
            </div>
        @else
            <div class="space-y-2 account-list" style="opacity: 0;">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $type = $data['type'] ?? 'info';
                    @endphp
                    <div class="bg-white rounded-xl border border-gray-100 px-5 py-4 flex items-start gap-4 notification-card {{ $notification->read_at ? '' : 'border-l-4 border-l-emerald-500' }}" data-gsap="notification">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                            @if($type === 'order') bg-blue-50
                            @elseif($type === 'shipping') bg-indigo-50
                            @elseif($type === 'promo') bg-pink-50
                            @elseif($type === 'wallet') bg-amber-50
                            @else bg-gray-50 @endif">
                            @if($type === 'order')
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            @elseif($type === 'shipping')
                                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            @elseif($type === 'promo')
                                <svg class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            @elseif($type === 'wallet')
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900">{{ $data['title'] ?? __('Notification') }}</p>
                            @if(isset($data['message']))
                                <p class="text-[12px] text-gray-500 mt-0.5">{{ $data['message'] }}</p>
                            @endif
                            <p class="text-[11px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!$notification->read_at)
                            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full flex-shrink-0 mt-1.5"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.account-header, .account-empty, .account-list, .notification-card').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.account-header', '.account-empty', '.account-list'], { y: 20 });
            tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
              .to(['.account-empty', '.account-list'], { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.notification-card', { opacity: 1, y: 0, duration: 0.3, stagger: 0.05 }, '-=0.3');
        });
    </script>
    @endpush
</x-account.layout>
