<?php

namespace App\Providers;

use App\Guards\ApiTokenGuard;
use App\Models\ApiToken;
use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Services\Courier\CourierManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CourierManager::class, function ($app) {
            return new CourierManager();
        });
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);

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
