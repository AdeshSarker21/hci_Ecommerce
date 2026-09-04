<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['account' => 'Your account has been deactivated.']);
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}
