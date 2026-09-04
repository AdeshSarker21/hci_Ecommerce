@props(['title' => null, 'subtitle' => null])

<div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex items-center space-x-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
