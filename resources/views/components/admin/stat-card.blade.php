@props(['title', 'value', 'change' => null, 'changeType' => 'neutral', 'icon' => 'chart', 'color' => 'indigo'])

@php
    $colorMap = [
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'blue'   => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'green'  => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber'  => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'red'    => ['bg' => 'bg-red-50', 'text' => 'text-red-600'],
        'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
    ];
    $c = $colorMap[$color] ?? $colorMap['indigo'];
@endphp

<div class="bg-white rounded-xl border border-gray-100 p-5 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $title }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1.5">{{ $value }}</p>
            @if($change !== null)
                <div class="flex items-center mt-2">
                    @if($changeType === 'up')
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span class="text-xs font-medium text-emerald-600 ml-1">{{ $change }}</span>
                    @elseif($changeType === 'down')
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span class="text-xs font-medium text-red-600 ml-1">{{ $change }}</span>
                    @else
                        <span class="text-xs font-medium text-gray-400">{{ $change }}</span>
                    @endif
                </div>
            @endif
        </div>
        <div class="w-11 h-11 {{ $c['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
            @switch($icon)
                @case('users')
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    @break
                @case('shopping-cart')
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                    </svg>
                    @break
                @case('cube')
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    @break
                @case('currency')
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @break
                @case('store')
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    @break
                @case('trending-up')
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    @break
                @default
                    <svg class="w-5 h-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
            @endswitch
        </div>
    </div>
</div>
