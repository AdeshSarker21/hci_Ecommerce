@props(['title' => 'Home', 'description' => null, 'image' => null])

@php
    $categories = \App\Models\Category::whereNull('parent_id')->active()->ordered()->limit(12)->get();
    $metaTitle = $title . ' - ' . config('app.name');
    $metaDescription = $description ?? __('Your premium destination for quality products from verified sellers worldwide.');
    $metaImage = $image ?? null;
    $canonical = request()->url();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    @if($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if($metaImage)
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .gsap-hidden { opacity: 0; transform: translateY(30px); }
        .gsap-hidden-left { opacity: 0; transform: translateX(-40px); }
        .gsap-hidden-right { opacity: 0; transform: translateX(40px); }
        .gsap-hidden-scale { opacity: 0; transform: scale(0.92); }
        .slide-active { opacity: 1 !important; transform: none !important; }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        .shimmer { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        .float-anim { animation: float 3s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } .gsap-hidden, .gsap-hidden-left, .gsap-hidden-right, .gsap-hidden-scale { opacity: 1; transform: none; } }
    </style>
</head>
<body class="bg-[#fafafa] text-gray-900 antialiased">
    {{ $slot }}

    <x-toast />
    <x-cart.mini-cart />

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
