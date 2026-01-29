<?php

use App\Http\Middleware\ApiTokenAuth;
use App\Http\Middleware\CustomerTenantScope;
use App\Http\Middleware\EnsureCustomerPortalEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('portal')
                ->group(base_path('routes/portal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.token' => ApiTokenAuth::class,
            'portal.enabled' => EnsureCustomerPortalEnabled::class,
            'portal.tenant' => CustomerTenantScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
