<?php

namespace App\Providers;

use App\Services\DeviceIngestService;
use App\Services\DeviceReportService;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DeviceIngestService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(DeviceReportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
