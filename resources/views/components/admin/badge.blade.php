@props(['type' => 'gray', 'label' => ''])

@php
    $typeClasses = match($type) {
        'gray'   => 'bg-gray-100 text-gray-700',
        'green'  => 'bg-emerald-100 text-emerald-700',
        'red'    => 'bg-red-100 text-red-700',
        'amber'  => 'bg-amber-100 text-amber-700',
        'blue'   => 'bg-blue-100 text-blue-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'purple' => 'bg-purple-100 text-purple-700',
        default  => 'bg-gray-100 text-gray-700',
    };
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeClasses }}">
    {{ $label }}
</span>
