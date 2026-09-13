<x-account.layout :title="__('Dashboard')" active="dashboard">
    @php $locale = app()->getLocale(); @endphp

    <div class="space-y-6">

        {{-- Welcome Header --}}
        <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl p-6 sm:p-8 text-white account-welcome" style="opacity: 0;">
            <h1 class="text-[22px] sm:text-[26px] font-bold">{{ __('Welcome back') }}, {{ $user->name }}!</h1>
            <p class="text-emerald-100 text-[13px] mt-1">{{ __('Manage your orders, wishlist, and account settings from here.') }}</p>
            <div class="flex flex-wrap gap-3 mt-5">
                <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm text-white text-[12px] font-semibold rounded-xl hover:bg-white/30 transition-colors border border-white/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ __('View Orders') }}
                </a>
                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-emerald-600 text-[12px] font-semibold rounded-xl hover:bg-emerald-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    {{ __('Continue Shopping') }}
                </a>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 account-stats" style="opacity: 0;">
            <a href="{{ route('account.orders') }}" class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md hover:border-emerald-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="text-[20px] font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">{{ __('Total Orders') }}</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('account.orders') }}" class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md hover:border-amber-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[20px] font-bold text-amber-600">{{ $stats['pending_orders'] }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">{{ __('Pending') }}</p>
                    </div>
                </div>
            </a>

            <div class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <div>
                        <p class="text-[20px] font-bold text-gray-900">{{ $stats['shipped_orders'] }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">{{ __('Shipped') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[20px] font-bold text-emerald-600">{{ $stats['delivered_orders'] }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">{{ __('Delivered') }}</p>
                    </div>
                </div>
            </div>

            <a href="{{ route('account.wishlist') }}" class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md hover:border-pink-200 transition-all group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-pink-50 rounded-xl flex items-center justify-center group-hover:bg-pink-100 transition-colors">
                        <svg class="w-5 h-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[20px] font-bold text-pink-600">{{ $stats['wishlist_count'] }}</p>
                        <p class="text-[10px] text-gray-400 font-medium">{{ __('Wishlist') }}</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Recent Orders --}}
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden account-recent" style="opacity: 0;">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-gray-900">{{ __('Recent Orders') }}</h2>
                <a href="{{ route('account.orders') }}" class="text-[12px] font-medium text-emerald-600 hover:text-emerald-700 transition-colors">{{ __('View All') }}</a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-[13px] text-gray-500">{{ __('No orders yet.') }}</p>
                    <a href="{{ route('search') }}" class="mt-3 inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 hover:text-emerald-700">
                        {{ __('Start Shopping') }}
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($recentOrders as $order)
                        <a href="{{ route('account.orders.show', $order) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/50 transition-colors order-row" style="opacity: 0;">
                            <div class="flex -space-x-2 flex-shrink-0">
                                @foreach($order->items->take(2) as $item)
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg border-2 border-white overflow-hidden">
                                        @if($item->product?->primary_image)
                                            <img src="{{ asset('storage/' . $item->product->primary_image) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-[13px] font-bold text-gray-900">{{ $order->order_number }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <p class="text-[12px] text-gray-400 mt-0.5 truncate">
                                    @foreach($order->items->take(2) as $item)
                                        {{ $locale === 'bn' && $item->product?->name_bn ? $item->product->name_bn : $item->product_name }}@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            </div>

                            <div class="text-right flex-shrink-0">
                                <p class="text-[14px] font-bold text-gray-900">${{ number_format($order->total, 2) }}</p>
                                <p class="text-[11px] text-gray-400">{{ $order->created_at->format('M d') }}</p>
                            </div>

                            <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 account-actions" style="opacity: 0;">
            <a href="{{ route('account.profile') }}" class="bg-white rounded-xl border border-gray-100 p-4 text-center hover:shadow-md hover:border-emerald-200 transition-all group">
                <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <p class="text-[12px] font-semibold text-gray-700">{{ __('Edit Profile') }}</p>
            </a>
            <a href="{{ route('account.addresses') }}" class="bg-white rounded-xl border border-gray-100 p-4 text-center hover:shadow-md hover:border-emerald-200 transition-all group">
                <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <p class="text-[12px] font-semibold text-gray-700">{{ __('Addresses') }}</p>
            </a>
            <a href="{{ route('account.reviews') }}" class="bg-white rounded-xl border border-gray-100 p-4 text-center hover:shadow-md hover:border-emerald-200 transition-all group">
                <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <p class="text-[12px] font-semibold text-gray-700">{{ __('Reviews') }}</p>
            </a>
            <a href="{{ route('account.wallet') }}" class="bg-white rounded-xl border border-gray-100 p-4 text-center hover:shadow-md hover:border-emerald-200 transition-all group">
                <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto mb-2 group-hover:bg-emerald-50 transition-colors">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <p class="text-[12px] font-semibold text-gray-700">{{ __('Wallet') }}</p>
            </a>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.account-welcome, .account-stats, .account-recent, .account-actions, .order-row').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.account-welcome', '.account-stats', '.account-recent', '.account-actions'], { y: 20 });

            tl.to('.account-welcome', { opacity: 1, y: 0, duration: 0.5 })
              .to('.account-stats', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.account-recent', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.order-row', { opacity: 1, y: 0, duration: 0.3, stagger: 0.06 }, '-=0.3')
              .to('.account-actions', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3');
        });
    </script>
    @endpush
</x-account.layout>
