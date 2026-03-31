<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Offline Threshold (minutes)
    |--------------------------------------------------------------------------
    | Jumlah menit tanpa heartbeat sebelum device dianggap offline.
    | Watchdog job CheckOfflineDevices menggunakan nilai ini.
    */
    'offline_threshold_minutes' => env('DEVICE_OFFLINE_THRESHOLD_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Default Thresholds
    |--------------------------------------------------------------------------
    | Nilai default untuk device baru jika tidak diset manual.
    */
    'defaults' => [
        'temperature_min'    => 18,
        'temperature_max'    => 25,
        'battery_threshold'   => 20,
        'heartbeat_interval'  => 300, // detik
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention (days)
    |--------------------------------------------------------------------------
    | Berapa hari log dipertahankan di database. Older logs akan diprune.
    */
    'log_retention_days' => env('DEVICE_LOG_RETENTION_DAYS', 30),
];
