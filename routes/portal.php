<?php

use App\Http\Controllers\Portal\Auth\LoginController;
use App\Http\Controllers\Portal\CdrController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\IpController;
use App\Http\Controllers\Portal\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest:customer')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('portal.login');
    Route::post('login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware(['auth:customer', 'portal.enabled', 'portal.tenant'])->group(function () {
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('portal.logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('portal.dashboard');

    // CDRs
    Route::prefix('cdrs')->name('portal.cdrs.')->group(function () {
        Route::get('/', [CdrController::class, 'index'])->name('index');
        Route::get('/export', [CdrController::class, 'export'])->name('export');
        Route::get('/{cdr}', [CdrController::class, 'show'])->name('show');
    });

    // IPs
    Route::prefix('ips')->name('portal.ips.')->group(function () {
        Route::get('/', [IpController::class, 'index'])->name('index');
        Route::get('/request', [IpController::class, 'createRequest'])->name('create');
        Route::post('/request', [IpController::class, 'storeRequest'])->name('store');
        Route::delete('/request/{request}', [IpController::class, 'cancelRequest'])->name('cancel');
    });

    // Profile
    Route::prefix('profile')->name('portal.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'showChangePassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'changePassword'])->name('password.update');
    });
});
