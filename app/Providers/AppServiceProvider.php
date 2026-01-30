<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\Carrier;
use App\Models\Cdr;
use App\Models\CustomerIp;
use App\Observers\AlertObserver;
use App\Observers\CarrierObserver;
use App\Observers\CdrObserver;
use App\Observers\CustomerIpObserver;
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
        Cdr::observe(CdrObserver::class);
        CustomerIp::observe(CustomerIpObserver::class);
    }
}
