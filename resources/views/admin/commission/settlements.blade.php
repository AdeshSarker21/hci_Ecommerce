<x-admin.layout title="Settlements" active="settlements">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Settlements</h2>
        <p class="text-sm text-gray-500 mt-1">Manage seller settlements and payout requests</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($settlements->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Settlement #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($settlements as $settlement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-emerald-600">#{{ $settlement->settlement_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $settlement->seller?->store_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ number_format($settlement->amount, 2) }}</td>
                                <td class="px-6 py-4"><span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $settlement->status_badge }}">{{ ucfirst($settlement->status) }}</span></td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $settlement->notes ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $settlement->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    @if(in_array($settlement->status, ['pending', 'processing']))
                                        <form method="POST" action="{{ route('admin.commission.settlements.complete', $settlement) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-700 font-medium">Complete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $settlements->links() }}</div>
        @else
            <div class="px-6 py-16 text-center"><p class="text-gray-500">No settlements found</p></div>
        @endif
    </div>
</x-admin.layout>
