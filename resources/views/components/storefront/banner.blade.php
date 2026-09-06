@props([
    'href' => '#',
    'tagline' => null,
    'title',
    'description' => null,
    'cta' => 'Shop Now',
    'gradient' => 'from-emerald-500 to-teal-600',
    'shadowColor' => 'emerald',
])

<a href="{{ $href }}" class="group relative block bg-gradient-to-br {{ $gradient }} rounded-2xl p-8 lg:p-10 overflow-hidden shadow-lg hover:shadow-xl transition-all duration-500 hover:-translate-y-0.5 {{ $shadowColor }}">
    <div class="absolute right-0 top-0 w-48 h-48 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4"></div>
    <div class="absolute right-8 bottom-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2"></div>
    <div class="relative z-10">
        @if($tagline)
            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold rounded-full uppercase tracking-wider mb-4">{{ $tagline }}</span>
        @endif
        <h3 class="text-xl lg:text-2xl font-bold text-white mb-2">{{ $title }}</h3>
        @if($description)
            <p class="text-[13px] text-white/70 mb-5">{{ $description }}</p>
        @endif
        <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-white">{{ $cta }} <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
    </div>
</a>
