<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AlertsController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DeviceController;
use App\Http\Controllers\Web\DeviceTesterController;
use App\Http\Controllers\Web\OutletController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\UserController;
use App\Http\Middleware\EnsureOwnerRole;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes (Inertia / Vue 3)
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => Inertia::render('Auth/Login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Device Tester (dummy testing)
    Route::get('/device-tester', [DeviceTesterController::class, 'index'])->name('device-tester');

    // Alerts
    Route::get('/alerts', [AlertsController::class, 'index'])->name('alerts');
    Route::post('/alerts/{alert}/resolve', [AlertsController::class, 'resolve'])->name('alerts.resolve');
    Route::post('/alerts/bulk-resolve', [AlertsController::class, 'bulkResolve'])->name('alerts.bulk-resolve');

    // Devices
    Route::resource('devices', DeviceController::class);
    Route::patch('/devices/{device}/toggle', [DeviceController::class, 'toggle'])->name('devices.toggle');

    // Outlets
    Route::resource('outlets', OutletController::class);
    Route::get('/outlets/import/template', [OutletController::class, 'importTemplate'])->name('outlets.import.template');
    Route::post('/outlets/import', [OutletController::class, 'import'])->name('outlets.import');
    Route::post('/outlets/{outlet}/devices', [OutletController::class, 'assignDevice'])->name('outlets.devices.assign');
    Route::delete('/outlets/{outlet}/devices/{device}', [OutletController::class, 'unassignDevice'])->name('outlets.devices.unassign');

    // Users (Owner only)
    Route::middleware(EnsureOwnerRole::class)->group(function () {
        Route::resource('users', UserController::class);
    });

    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
});

