<x-account.layout :title="__('My Orders')" active="orders">
    @php $locale = app()->getLocale(); @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('My Orders') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('Track and manage your orders') }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 account-stats" style="opacity: 0;">
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-[20px] font-bold text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-[10px] text-gray-400 font-medium">{{ __('Total') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-[20px] font-bold text-amber-600">{{ $stats['pending'] }}</p>
                <p class="text-[10px] text-gray-400 font-medium">{{ __('Pending') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-[20px] font-bold text-blue-600">{{ $stats['processing'] }}</p>
                <p class="text-[10px] text-gray-400 font-medium">{{ __('Processing') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-[20px] font-bold text-indigo-600">{{ $stats['shipped'] }}</p>
                <p class="text-[10px] text-gray-400 font-medium">{{ __('Shipped') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-[20px] font-bold text-emerald-600">{{ $stats['delivered'] }}</p>
                <p class="text-[10px] text-gray-400 font-medium">{{ __('Delivered') }}</p>
            </div>
        </div>

        {{-- Orders List --}}
        @if($orders->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No orders yet') }}</h3>
                <p class="text-[13px] text-gray-500 mb-5">{{ __('Start shopping to see your orders here.') }}</p>
                <a href="{{ route('search') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-[13px] font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                    {{ __('Browse Products') }}
                </a>
            </div>
        @else
            <div class="space-y-3 account-list" style="opacity: 0;">
                @foreach($orders as $order)
                    <a href="{{ route('account.orders.show', $order) ?? '#' }}" class="block bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md hover:border-emerald-200 transition-all duration-200 order-card" style="opacity: 0;">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-5 py-3 bg-gray-50 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <p class="text-[13px] font-bold text-gray-900">{{ $order->order_number }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $order->status_badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <p class="text-[12px] text-gray-400">{{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex -space-x-2 flex-shrink-0">
                                    @foreach($order->items->take(3) as $item)
                                        <div class="w-12 h-12 bg-gray-100 rounded-xl border-2 border-white overflow-hidden">
                                            @if($item->product?->primary_image)
                                                <img src="{{ asset('storage/' . $item->product->primary_image) }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] text-gray-700 line-clamp-1">
                                        @foreach($order->items->take(2) as $item)
                                            {{ $locale === 'bn' && $item->product?->name_bn ? $item->product->name_bn : $item->product_name }}@if(!$loop->last), @endif
                                        @endforeach
                                        @if($order->items_count > 2)
                                            <span class="text-gray-400">+{{ $order->items_count - 2 }} {{ __('more') }}</span>
                                        @endif
                                    </p>
                                    <p class="text-[12px] text-gray-400 mt-1">{{ __('Sold by') }}: {{ $order->seller?->store_name ?? __('Unknown') }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-[16px] font-bold text-gray-900">${{ number_format($order->total, 2) }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $order->items_count }} {{ __('item(s)') }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.account-header, .account-stats, .account-empty, .account-list, .order-card').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.account-header', '.account-stats', '.account-empty', '.account-list'], { y: 20 });
            tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
              .to('.account-stats', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to(['.account-empty', '.account-list'], { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.order-card', { opacity: 1, y: 0, duration: 0.3, stagger: 0.06 }, '-=0.3');
        });
    </script>
    @endpush
</x-account.layout>
