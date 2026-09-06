@props([
    'title',
    'titleEn' => null,
    'subtitle' => null,
    'subtitleEn' => null,
    'accent' => 'emerald',
    'viewAllHref' => null,
    'viewAllText' => 'View All',
])

@php
    $locale = app()->getLocale();
    $displayTitle = $locale === 'bn' && $titleEn ? $titleEn : $title;
    $displaySubtitle = $locale === 'bn' && $subtitleEn ? $subtitleEn : $subtitle;

    $accentMap = [
        'emerald' => ['text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
        'red' => ['text' => 'text-red-500', 'bg' => 'bg-red-50'],
        'indigo' => ['text' => 'text-indigo-500', 'bg' => 'bg-indigo-50'],
        'amber' => ['text' => 'text-amber-500', 'bg' => 'bg-amber-50'],
        'violet' => ['text' => 'text-violet-500', 'bg' => 'bg-violet-50'],
        'gray' => ['text' => 'text-gray-400', 'bg' => 'bg-gray-50'],
    ];
    $colors = $accentMap[$accent] ?? $accentMap['emerald'];
@endphp

<div class="flex items-end justify-between mb-8">
    <div>
        <p class="text-[11px] font-bold {{ $colors['text'] }} uppercase tracking-widest mb-1">{{ $displayTitle }}</p>
        @if($displaySubtitle)
            <h2 class="text-[22px] font-bold text-gray-900">{{ $displaySubtitle }}</h2>
        @else
            <h2 class="text-[22px] font-bold text-gray-900">{{ $displayTitle }}</h2>
        @endif
    </div>
    @if($viewAllHref)
        <a href="{{ $viewAllHref }}" class="hidden sm:inline-flex items-center gap-1 text-[13px] font-semibold {{ $colors['text'] }} hover:opacity-80 transition-colors">
            {{ $viewAllText }}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    @endif
</div>
