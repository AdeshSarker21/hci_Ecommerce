<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ThrottleLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.role' => CheckRole::class,
            'check.permission' => CheckPermission::class,
            'ensure.active' => EnsureUserIsActive::class,
            'throttle.login' => ThrottleLogin::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*')) {
                return route('login');
            }
            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();
            if (!$user) {
                return '/dashboard';
            }
            if ($user->hasAnyRole(['super-admin', 'admin', 'manager', 'product-manager'])) {
                return '/admin';
            }
            if ($user->hasAnyRole(['seller', 'seller-staff'])) {
                return '/seller';
            }
            return '/dashboard';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
