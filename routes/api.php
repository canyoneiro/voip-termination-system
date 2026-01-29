<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\CdrController;
use App\Http\Controllers\Api\ActiveCallController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\SystemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public endpoints
Route::prefix('v1')->group(function () {
    Route::get('/health', [SystemController::class, 'health']);
    Route::get('/ping', fn() => response()->json(['pong' => true]));
});

// Protected API endpoints (TODO: Add proper authentication middleware)
Route::prefix('v1')->group(function () {

    // Customers
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::patch('/{id}/status', [CustomerController::class, 'updateStatus']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
        Route::get('/{id}/ips', [CustomerController::class, 'ips']);
        Route::post('/{id}/ips', [CustomerController::class, 'addIp']);
        Route::delete('/{id}/ips/{ipId}', [CustomerController::class, 'removeIp']);
        Route::get('/{id}/active-calls', [CustomerController::class, 'activeCalls']);
        Route::get('/{id}/usage', [CustomerController::class, 'usage']);
        Route::post('/{id}/reset-minutes', [CustomerController::class, 'resetMinutes']);
    });

    // Carriers
    Route::prefix('carriers')->group(function () {
        Route::get('/', [CarrierController::class, 'index']);
        Route::post('/', [CarrierController::class, 'store']);
        Route::get('/{id}', [CarrierController::class, 'show']);
        Route::put('/{id}', [CarrierController::class, 'update']);
        Route::patch('/{id}/status', [CarrierController::class, 'updateStatus']);
        Route::delete('/{id}', [CarrierController::class, 'destroy']);
        Route::get('/{id}/status', [CarrierController::class, 'status']);
    });

    // CDRs
    Route::prefix('cdrs')->group(function () {
        Route::get('/', [CdrController::class, 'index']);
        Route::get('/export', [CdrController::class, 'export']);
        Route::get('/{uuid}', [CdrController::class, 'show']);
        Route::get('/{uuid}/trace', [CdrController::class, 'trace']);
    });

    // Active Calls
    Route::prefix('active-calls')->group(function () {
        Route::get('/', [ActiveCallController::class, 'index']);
        Route::get('/count', [ActiveCallController::class, 'count']);
        Route::get('/{callId}', [ActiveCallController::class, 'show']);
    });

    // Stats
    Route::prefix('stats')->group(function () {
        Route::get('/realtime', [StatsController::class, 'realtime']);
        Route::get('/summary', [StatsController::class, 'summary']);
        Route::get('/hourly', [StatsController::class, 'hourly']);
        Route::get('/daily', [StatsController::class, 'daily']);
        Route::get('/by-customer', [StatsController::class, 'byCustomer']);
        Route::get('/by-carrier', [StatsController::class, 'byCarrier']);
        Route::get('/top-destinations', [StatsController::class, 'topDestinations']);
    });

    // Alerts
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index']);
        Route::get('/unacknowledged/count', [AlertController::class, 'unacknowledgedCount']);
        Route::get('/{id}', [AlertController::class, 'show']);
        Route::patch('/{id}/acknowledge', [AlertController::class, 'acknowledge']);
        Route::post('/acknowledge-bulk', [AlertController::class, 'acknowledgeBulk']);
    });

    // System
    Route::prefix('system')->group(function () {
        Route::get('/status', [SystemController::class, 'status']);
        Route::get('/config', [SystemController::class, 'config']);
        Route::post('/kamailio/reload', [SystemController::class, 'reloadKamailio']);
        Route::post('/cache/flush', [SystemController::class, 'flushCache']);
    });

    // Blacklist
    Route::prefix('blacklist')->group(function () {
        Route::get('/', [SystemController::class, 'blacklist']);
        Route::post('/', [SystemController::class, 'addToBlacklist']);
        Route::delete('/{id}', [SystemController::class, 'removeFromBlacklist']);
    });
});
