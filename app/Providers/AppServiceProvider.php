<?php

namespace App\Providers;

use App\Services\HttpService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);

        // Register HttpService as a singleton
        $this->app->singleton(HttpService::class, function ($app) {
            return new HttpService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
