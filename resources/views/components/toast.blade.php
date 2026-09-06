{{-- Toast Notification Component --}}
<div x-data x-show="$store.cart.toastVisible"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     x-cloak
     class="fixed bottom-6 right-6 z-[9999] max-w-sm">
    <div class="flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl border"
         :class="$store.cart.toastType === 'success'
             ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
             : 'bg-red-50 border-red-200 text-red-800'">
        <template x-if="$store.cart.toastType === 'success'">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </template>
        <template x-if="$store.cart.toastType === 'error'">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </template>
        <span class="text-sm font-medium" x-text="$store.cart.toastMessage"></span>
        <button @click="$store.cart.toastVisible = false" class="ml-auto opacity-60 hover:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
