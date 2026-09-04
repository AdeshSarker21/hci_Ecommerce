@props(['href' => null, 'method' => 'GET', 'type' => 'primary', 'size' => 'md', 'class' => '', 'disabled' => false])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };
    $typeClasses = match($type) {
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        default => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    };
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';
@endphp

@if($href && $method === 'GET')
    <a href="{{ $href }}" class="{{ $baseClasses }} {{ $sizeClasses }} {{ $typeClasses }} {{ $disabledClasses }} {{ $class }}" @if($disabled) aria-disabled="true" @endif>
        {{ $slot }}
    </a>
@elseif($href)
    <form method="POST" action="{{ $href }}" class="inline-block" x-data>
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif
        <button type="submit" class="{{ $baseClasses }} {{ $sizeClasses }} {{ $typeClasses }} {{ $disabledClasses }} {{ $class }}" @if($disabled) disabled @endif>
            {{ $slot }}
        </button>
    </form>
@else
    <button type="submit" class="{{ $baseClasses }} {{ $sizeClasses }} {{ $typeClasses }} {{ $disabledClasses }} {{ $class }}" @if($disabled) disabled @endif>
        {{ $slot }}
    </button>
@endif
