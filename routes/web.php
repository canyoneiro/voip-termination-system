<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AlertController;
use App\Http\Controllers\Web\BlacklistController;
use App\Http\Controllers\Web\CarrierController;
use App\Http\Controllers\Web\CdrController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DialingPlanController;
use App\Http\Controllers\Web\FraudController;
use App\Http\Controllers\Web\QosController;
use App\Http\Controllers\Web\RateController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/ips', [CustomerController::class, 'addIp'])->name('customers.add-ip');
    Route::delete('customers/{customer}/ips/{ip}', [CustomerController::class, 'removeIp'])->name('customers.remove-ip');
    Route::post('customers/{customer}/reset-minutes', [CustomerController::class, 'resetMinutes'])->name('customers.reset-minutes');

    // Carriers
    Route::resource('carriers', CarrierController::class);
    Route::post('carriers/{carrier}/test', [CarrierController::class, 'test'])->name('carriers.test');

    // Dialing Plans
    Route::resource('dialing-plans', DialingPlanController::class);
    Route::post('dialing-plans/{dialing_plan}/rules', [DialingPlanController::class, 'storeRule'])->name('dialing-plans.rules.store');
    Route::put('dialing-plans/{dialing_plan}/rules/{rule}', [DialingPlanController::class, 'updateRule'])->name('dialing-plans.rules.update');
    Route::delete('dialing-plans/{dialing_plan}/rules/{rule}', [DialingPlanController::class, 'destroyRule'])->name('dialing-plans.rules.destroy');
    Route::post('dialing-plans/{dialing_plan}/test', [DialingPlanController::class, 'testNumber'])->name('dialing-plans.test');
    Route::post('dialing-plans/{dialing_plan}/clone', [DialingPlanController::class, 'clone'])->name('dialing-plans.clone');
    Route::post('dialing-plans/{dialing_plan}/import-rules', [DialingPlanController::class, 'importRules'])->name('dialing-plans.rules.import');

    // CDRs
    Route::get('cdrs', [CdrController::class, 'index'])->name('cdrs.index');
    Route::get('cdrs/export', [CdrController::class, 'export'])->name('cdrs.export');
    Route::get('cdrs/{cdr}', [CdrController::class, 'show'])->name('cdrs.show');

    // Alerts
    Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('alerts/{alert}', [AlertController::class, 'show'])->name('alerts.show');
    Route::post('alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::post('alerts/acknowledge-multiple', [AlertController::class, 'acknowledgeMultiple'])->name('alerts.acknowledge-multiple');
    Route::delete('alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');

    // Blacklist
    Route::get('blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
    Route::post('blacklist', [BlacklistController::class, 'store'])->name('blacklist.store');
    Route::delete('blacklist/{blacklist}', [BlacklistController::class, 'destroy'])->name('blacklist.destroy');
    Route::post('blacklist/{blacklist}/toggle-permanent', [BlacklistController::class, 'togglePermanent'])->name('blacklist.toggle-permanent');

    // Webhooks
    Route::resource('webhooks', WebhookController::class);
    Route::post('webhooks/{webhook}/regenerate-secret', [WebhookController::class, 'regenerateSecret'])->name('webhooks.regenerate-secret');
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
    Route::get('webhooks/{webhook}/deliveries', [WebhookController::class, 'deliveries'])->name('webhooks.deliveries');

    // QoS
    Route::prefix('qos')->name('qos.')->group(function () {
        Route::get('/', [QosController::class, 'index'])->name('index');
        Route::get('/customer/{customer}', [QosController::class, 'customer'])->name('customer');
        Route::get('/carrier/{carrier}', [QosController::class, 'carrier'])->name('carrier');
    });

    // Fraud Detection
    Route::prefix('fraud')->name('fraud.')->group(function () {
        Route::get('/', [FraudController::class, 'index'])->name('index');
        Route::get('/incidents', [FraudController::class, 'incidents'])->name('incidents');
        Route::get('/incidents/{incident}', [FraudController::class, 'showIncident'])->name('incidents.show');
        Route::put('/incidents/{incident}', [FraudController::class, 'updateIncident'])->name('incidents.update');
        Route::get('/rules', [FraudController::class, 'rules'])->name('rules');
        Route::get('/rules/create', [FraudController::class, 'createRule'])->name('rules.create');
        Route::post('/rules', [FraudController::class, 'storeRule'])->name('rules.store');
        Route::get('/rules/{rule}/edit', [FraudController::class, 'editRule'])->name('rules.edit');
        Route::put('/rules/{rule}', [FraudController::class, 'updateRule'])->name('rules.update');
        Route::delete('/rules/{rule}', [FraudController::class, 'destroyRule'])->name('rules.destroy');
        Route::get('/risk-scores', [FraudController::class, 'riskScores'])->name('risk-scores');
    });

    // Scheduled Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/create', [ReportController::class, 'create'])->name('create');
        Route::post('/', [ReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [ReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [ReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');
        Route::post('/{report}/trigger', [ReportController::class, 'trigger'])->name('trigger');
        Route::get('/executions/{execution}', [ReportController::class, 'showExecution'])->name('executions.show');
        Route::get('/executions/{execution}/download/{format?}', [ReportController::class, 'downloadExecution'])->name('executions.download');
    });

    // Rates / LCR
    Route::prefix('rates')->name('rates.')->group(function () {
        Route::get('/', [RateController::class, 'index'])->name('index');

        // Destinations
        Route::get('/destinations', [RateController::class, 'destinations'])->name('destinations');
        Route::get('/destinations/create', [RateController::class, 'createDestination'])->name('destinations.create');
        Route::post('/destinations', [RateController::class, 'storeDestination'])->name('destinations.store');
        Route::get('/destinations/{destination}/edit', [RateController::class, 'editDestination'])->name('destinations.edit');
        Route::put('/destinations/{destination}', [RateController::class, 'updateDestination'])->name('destinations.update');
        Route::delete('/destinations/{destination}', [RateController::class, 'destroyDestination'])->name('destinations.destroy');

        // Carrier Rates
        Route::get('/carrier-rates', [RateController::class, 'carrierRates'])->name('carrier-rates');
        Route::get('/carrier-rates/create', [RateController::class, 'createCarrierRate'])->name('carrier-rates.create');
        Route::post('/carrier-rates', [RateController::class, 'storeCarrierRate'])->name('carrier-rates.store');
        Route::get('/carrier-rates/{rate}/edit', [RateController::class, 'editCarrierRate'])->name('carrier-rates.edit');
        Route::put('/carrier-rates/{rate}', [RateController::class, 'updateCarrierRate'])->name('carrier-rates.update');
        Route::delete('/carrier-rates/{rate}', [RateController::class, 'destroyCarrierRate'])->name('carrier-rates.destroy');

        // Rate Plans
        Route::get('/rate-plans', [RateController::class, 'ratePlans'])->name('rate-plans');
        Route::get('/rate-plans/create', [RateController::class, 'createRatePlan'])->name('rate-plans.create');
        Route::post('/rate-plans', [RateController::class, 'storeRatePlan'])->name('rate-plans.store');
        Route::get('/rate-plans/{ratePlan}', [RateController::class, 'showRatePlan'])->name('rate-plans.show');
        Route::get('/rate-plans/{ratePlan}/edit', [RateController::class, 'editRatePlan'])->name('rate-plans.edit');
        Route::put('/rate-plans/{ratePlan}', [RateController::class, 'updateRatePlan'])->name('rate-plans.update');
        Route::delete('/rate-plans/{ratePlan}', [RateController::class, 'destroyRatePlan'])->name('rate-plans.destroy');

        // LCR Test
        Route::get('/lcr-test', [RateController::class, 'lcrTest'])->name('lcr-test');
        Route::post('/lcr-test', [RateController::class, 'lcrLookup'])->name('lcr-lookup');

        // Import
        Route::get('/import', [RateController::class, 'importForm'])->name('import');
        Route::post('/import', [RateController::class, 'import'])->name('import.process');

        // Sync Redis
        Route::post('/sync-redis', [RateController::class, 'syncRedis'])->name('sync-redis');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
