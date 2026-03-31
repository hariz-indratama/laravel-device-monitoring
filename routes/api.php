<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeviceApiController;
use App\Http\Controllers\Api\V1\WilayahApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — Flutter Mobile & Device Ingestion
| Base URL: /api/v1
|--------------------------------------------------------------------------
*/

// Public wilayah endpoints (no auth required — reference data only)
Route::prefix('wilayah')->group(function () {
    Route::get('/provinces',  [WilayahApiController::class, 'provinces'])->name('api.v1.wilayah.provinces');
    Route::get('/cities',    [WilayahApiController::class, 'cities'])->name('api.v1.wilayah.cities');
    Route::get('/districts', [WilayahApiController::class, 'districts'])->name('api.v1.wilayah.districts');
    Route::get('/villages',  [WilayahApiController::class, 'villages'])->name('api.v1.wilayah.villages');
});

// Device ingestion — rate-limited to prevent abuse
Route::middleware('throttle:120,1')->group(function () {
    Route::post('/devices/{serial_number}/data', [DeviceApiController::class, 'ingest'])
        ->name('api.v1.devices.ingest');
});

// Login — rate-limited to prevent brute-force
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');
});

// Authenticated (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::get('/me', [AuthController::class, 'user'])->name('api.v1.me');

    // Mobile: Device info
    Route::get('/devices/{serial_number}', [DeviceApiController::class, 'show'])
        ->name('api.v1.devices.show');
});
