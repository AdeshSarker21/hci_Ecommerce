<x-layouts.app title="Dashboard">
    <div class="min-h-screen bg-slate-50">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h1 class="text-lg font-bold text-gray-900">{{ config('app.name') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager', 'product-manager']))
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 px-3 py-1.5 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">Admin Panel</a>
                        @endif
                        <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900">Welcome, {{ auth()->user()->name }}!</h2>
                    <p class="mt-1 text-sm text-gray-500">Here's your account overview.</p>
                    <div class="mt-4 p-4 bg-gray-50 rounded-xl">
                        <p class="text-sm font-medium text-gray-700">Your Roles</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @forelse(auth()->user()->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">{{ $role->name }}</span>
                            @empty
                                <span class="text-sm text-gray-500">No roles assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
