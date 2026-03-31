<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use App\Models\DeviceAlert;
use App\Models\DeviceLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_devices'   => Device::query()->forUser($user)->count(),
            'online_devices'  => Device::query()->forUser($user)->where('status', 'online')->count(),
            'offline_devices' => Device::query()->forUser($user)->where('status', 'offline')->count(),
            'warning_devices' => Device::query()->forUser($user)->where('status', 'warning')->count(),
            'active_alerts'  => DeviceAlert::query()
                ->forUser($user)
                ->whereNull('resolved_at')
                ->where('severity', '!=', 'info')
                ->count(),
        ];

        $recentAlerts = DeviceAlert::query()
            ->forUser($user)
            ->with('device')
            ->whereNull('resolved_at')
            ->latest()
            ->limit(10)
            ->get();

        $recentLogs = DeviceLog::query()
            ->forUser($user)
            ->with('device')
            ->latest('logged_at')
            ->limit(20)
            ->get();

        $devices = Device::query()
            ->forUser($user)
            ->with(['outlet', 'latestLog'])
            // Online devices first, then by most recent uptime_started_at
            ->orderByRaw("CASE WHEN status = 'online' THEN 0 ELSE 1 END")
            ->orderByDesc('uptime_started_at')
            ->limit(20)
            ->get()
            ->map(fn($d) => (new DeviceResource($d))->jsonSerialize());

        return Inertia::render('Dashboard/Index', [
            'stats'       => $stats,
            'recentAlerts'=> $recentAlerts,
            'recentLogs'  => $recentLogs,
            'devices'     => $devices,
        ]);
    }
}
