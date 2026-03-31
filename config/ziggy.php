<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ziggy URL
    |--------------------------------------------------------------------------
    |
    | This is the URL that your frontend can use to reach your Laravel
    | backend. This is used by Ziggy to generate URLs for named routes.
    |
    */
    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Ziggy Groups
    |--------------------------------------------------------------------------
    |
    | By default, Ziggy only exports routes with the 'web' or 'api' middleware.
    | You can specify which groups to export.
    |
    */
    'groups' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Strict mode
    |--------------------------------------------------------------------------
    |
    | When strict mode is enabled, Ziggy will not render links to routes
    | that are not registered in the application. When disabled, Ziggy
    | will render links to undefined routes as '#'.
    |
    */
    'strict' => false,
];
