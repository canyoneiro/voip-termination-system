<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\CarrierController;
use App\Http\Controllers\Web\CdrController;
use App\Http\Controllers\Web\AlertController;
use App\Http\Controllers\Web\BlacklistController;
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

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
