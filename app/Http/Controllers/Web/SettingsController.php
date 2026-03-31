<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Render the settings layout and general preferences.
     */
    public function index(Request $request)
    {
        // For now, we simulate user settings. 
        // In a real database, these could be persisted in a `users` table or a `user_settings` table.
        return Inertia::render('Settings/Index', [
            'settings' => [
                'notifications' => [
                    'email_alerts' => true,
                    'telegram_alerts' => true,
                    'weekly_report' => false,
                ],
                'appearance' => [
                    'theme' => 'system', // light, dark, system
                ],
                'locale' => 'en',
            ]
        ]);
    }
}
