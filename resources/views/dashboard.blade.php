<x-layouts.app>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-indigo-600">{{ config('app.name') }}</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if(auth()->user()->hasAnyRole(['super-admin', 'admin', 'manager', 'product-manager']))
                            <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Admin Panel</a>
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
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold">Dashboard</h2>
                        <p class="mt-2 text-gray-600">Welcome, {{ auth()->user()->name }}!</p>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">Roles: {{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
