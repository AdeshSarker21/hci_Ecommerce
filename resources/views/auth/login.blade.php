<x-layouts.app title="Login">
    <div class="min-h-screen flex">
        {{-- Left panel - branding --}}
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyem0wLTRWMjhIMjR2Mmgxem0tMi0ydi0ySDI0djJoMnptNC0ydjJoLTJ2LTJoMnptLTQtNHYyaC0yVjIwaDJ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
            <div class="relative z-10 flex flex-col items-center justify-center w-full p-12">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-8">
                    <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">{{ config('app.name') }}</h1>
                <p class="text-indigo-200 text-center text-lg max-w-sm">Powerful admin panel for managing your marketplace</p>
                <div class="mt-12 grid grid-cols-3 gap-6 text-center">
                    <div>
                        <p class="text-2xl font-bold text-white">100+</p>
                        <p class="text-sm text-indigo-200 mt-1">Features</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">24/7</p>
                        <p class="text-sm text-indigo-200 mt-1">Support</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">99.9%</p>
                        <p class="text-sm text-indigo-200 mt-1">Uptime</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right panel - form --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-white">
            <div class="w-full max-w-md">
                {{-- Mobile logo --}}
                <div class="lg:hidden flex items-center justify-center mb-8">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
                    <p class="text-sm text-gray-500 mt-1.5">Sign in to your account to continue</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all"
                               placeholder="you@example.com" value="{{ old('email') }}">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all"
                               placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                    </div>

                    @if ($errors->any())
                        <div class="p-3 bg-red-50 border border-red-200 rounded-xl">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-red-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-red-600">{{ $errors->first('email', 'Invalid credentials.') }}</p>
                            </div>
                        </div>
                    @endif

                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-sm">
                        Sign In
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Create account</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.app>
