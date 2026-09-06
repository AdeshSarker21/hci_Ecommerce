<x-seller.layout title="Earnings" active="commission">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Earnings Overview</h2>
        <p class="text-sm text-gray-500 mt-1">Your revenue breakdown and monthly earnings</p>
    </div>

    {{-- Balance Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Balance</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">${{ $balance['pending'] > 0 ? number_format($balance['pending'], 2) : '0.00' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting settlement</p>
                </div>
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Available Balance</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">${{ $balance['available'] > 0 ? number_format($balance['available'], 2) : '0.00' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Ready for withdrawal</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Withdrawn</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">${{ $balance['withdrawn'] > 0 ? number_format($balance['withdrawn'], 2) : '0.00' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total withdrawn</p>
                </div>
                <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Earned</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">${{ $balance['total_earned'] > 0 ? number_format($balance['total_earned'], 2) : '0.00' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Lifetime earnings</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Earnings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Earnings</h3>
        @if($monthlyEarnings->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earnings</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($monthlyEarnings as $month)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($month->month . '-01')->format('F Y') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $month->orders }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-green-600">${{ number_format($month->earnings, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No earnings data yet</p>
        @endif
    </div>
</x-seller.layout>
