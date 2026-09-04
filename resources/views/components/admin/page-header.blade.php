@props(['title' => ''])

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            @if($title)
                <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
            @endif
            @if(isset($subtitle))
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex items-center space-x-3">
            {{ $actions ?? '' }}
        </div>
    </div>
</div>
