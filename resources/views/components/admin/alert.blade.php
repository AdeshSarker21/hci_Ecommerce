@props(['message' => null])

@if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-green-700">{{ $message ?: session('success') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-red-700">{{ $message ?: session('error') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center mb-2">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-red-700">Please fix the following errors:</p>
        </div>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
