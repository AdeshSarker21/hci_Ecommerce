<x-account.layout :title="__('Wallet')" active="wallet">
    @php $locale = app()->getLocale(); @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="account-header" style="opacity: 0;">
            <h1 class="text-[22px] font-bold text-gray-900">{{ __('My Wallet') }}</h1>
            <p class="text-[13px] text-gray-500 mt-1">{{ __('View your wallet balance and transaction history.') }}</p>
        </div>

        {{-- Wallet Balance Card --}}
        <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl p-6 sm:p-8 text-white account-balance" style="opacity: 0;">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div>
                    <p class="text-emerald-100 text-[12px] font-medium">{{ __('Available Balance') }}</p>
                    <p class="text-[28px] font-bold">${{ number_format($wallet->available_balance ?? 0, 2) }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-white/20">
                <div>
                    <p class="text-emerald-100 text-[11px]">{{ __('Pending') }}</p>
                    <p class="text-[16px] font-bold">${{ number_format($wallet->pending_balance ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-emerald-100 text-[11px]">{{ __('Withdrawn') }}</p>
                    <p class="text-[16px] font-bold">${{ number_format($wallet->withdrawn_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-emerald-100 text-[11px]">{{ __('Total Earned') }}</p>
                    <p class="text-[16px] font-bold">${{ number_format($wallet->total_earned ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Transactions --}}
        @if($wallet && $wallet->transactions->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden account-transactions" style="opacity: 0;">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-[14px] font-bold text-gray-900">{{ __('Recent Transactions') }}</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($wallet->transactions->take(10) as $transaction)
                        <div class="px-5 py-4 flex items-center gap-4 transaction-item">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                                @if($transaction->type === 'credit') bg-emerald-50
                                @else bg-red-50 @endif">
                                @if($transaction->type === 'credit')
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-gray-900">{{ $transaction->description ?? $transaction->type }}</p>
                                <p class="text-[11px] text-gray-400">{{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <p class="text-[14px] font-bold {{ $transaction->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}${{ number_format(abs($transaction->amount), 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center account-empty" style="opacity: 0;">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h3 class="text-[16px] font-bold text-gray-900 mb-1">{{ __('No transactions yet') }}</h3>
                <p class="text-[13px] text-gray-500">{{ __('Your wallet transactions will appear here.') }}</p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof gsap === 'undefined') {
                document.querySelectorAll('.account-header, .account-balance, .account-transactions, .account-empty, .transaction-item').forEach(el => el.style.opacity = '1');
                return;
            }
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            gsap.set(['.account-header', '.account-balance', '.account-transactions', '.account-empty'], { y: 20 });
            tl.to('.account-header', { opacity: 1, y: 0, duration: 0.5 })
              .to('.account-balance', { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to(['.account-transactions', '.account-empty'], { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
              .to('.transaction-item', { opacity: 1, y: 0, duration: 0.3, stagger: 0.05 }, '-=0.3');
        });
    </script>
    @endpush
</x-account.layout>
