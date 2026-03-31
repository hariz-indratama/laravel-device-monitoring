<?php

use App\Jobs\CheckOfflineDevices;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes / Scheduler
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Watchdog: cek device offline setiap 5 menit
Schedule::job(new CheckOfflineDevices)->everyFiveMinutes();
