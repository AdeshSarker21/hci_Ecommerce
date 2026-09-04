<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            auth()->logout();
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Your account has been deactivated.'], 403);
            }
            return redirect()->route('login')->withErrors(['account' => 'Your account has been deactivated.']);
        }

        if (!$user->hasAnyPermission($permissions)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized. You do not have the required permission.'], 403);
            }
            abort(403, 'Unauthorized. You do not have the required permission.');
        }

        return $next($request);
    }
}
