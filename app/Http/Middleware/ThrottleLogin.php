<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login:' . ($request->input('email') ?: $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
                ], 429);
            }

            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ]);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);

        if ($response->isSuccessful() || $response->isRedirect()) {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
