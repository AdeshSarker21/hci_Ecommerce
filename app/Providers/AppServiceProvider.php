<?php

namespace App\Providers;

use App\Guards\ApiTokenGuard;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::provider('api-token', function ($app, array $config) {
            return new \Illuminate\Auth\EloquentUserProvider($app['hash'], User::class);
        });

        Auth::extend('api-token', function ($app, $name, array $config) {
            return new ApiTokenGuard(
                Auth::createUserProvider($config['provider']),
                $app['request']
            );
        });
    }
}
