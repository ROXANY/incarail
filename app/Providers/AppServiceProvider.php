<?php

namespace App\Providers;

use App\Contracts\FootballApiInterface;
use App\Services\FootballDataApi;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passport::ignoreRoutes();

        $this->app->bind(FootballApiInterface::class, FootballDataApi::class);
    }

    public function boot(): void {}
}
