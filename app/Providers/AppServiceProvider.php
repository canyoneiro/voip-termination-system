<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\Carrier;
use App\Observers\AlertObserver;
use App\Observers\CarrierObserver;
use App\Services\WebhookService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WebhookService::class, function ($app) {
            return new WebhookService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Alert::observe(AlertObserver::class);
        Carrier::observe(CarrierObserver::class);
    }
}
