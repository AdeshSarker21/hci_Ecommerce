@php
    $paginator = $paginator ?? $this->paginator ?? null;
    if (!$paginator) return;
@endphp

@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-1.5">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 text-gray-400 text-[13px] font-medium cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 text-[13px] font-medium hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-400 text-[13px] font-medium">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-600 text-white text-[13px] font-bold shadow-lg shadow-emerald-600/20">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 text-[13px] font-medium hover:bg-gray-50 hover:border-gray-300 transition-all">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 text-[13px] font-medium hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @else
            <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 text-gray-400 text-[13px] font-medium cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </nav>
@endif
