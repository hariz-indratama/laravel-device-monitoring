<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceAlert;
use App\Models\DeviceLog;
use App\Models\User;
use Illuminate\Support\Collection;

class DeviceReportService
{
    public function dailyReport(User $user): array
    {
        $devices = Device::query()->forUser($user)->with('logs')->get();

        $report = $devices->map(function (Device $device) {
            $todayLogs = $device->logs()
                ->whereDate('logged_at', today())
                ->get();

            return [
                'device'         => $device->name,
                'outlet'         => $device->outlet?->name,
                'status'         => $device->status->label(),
                'total_readings' => $todayLogs->count(),
                'alerts_count'   => $device->alerts()->whereDate('created_at', today())->count(),
            ];
        });

        return [
            'date'   => now()->toDateString(),
            'devices'=> $report->toArray(),
            'summary'=> [
                'total_devices'  => $devices->count(),
                'online'        => $devices->where('status', 'online')->count(),
                'offline'       => $devices->where('status', 'offline')->count(),
                'total_alerts'  => DeviceAlert::query()
                    ->forUser($user)
                    ->whereDate('created_at', today())
                    ->where('severity', '!=', 'info')
                    ->count(),
            ],
        ];
    }

    public function deviceHistory(int $deviceId, string $from, string $to): Collection
    {
        return DeviceLog::where('device_id', $deviceId)
            ->whereBetween('logged_at', [$from, $to])
            ->orderBy('logged_at')
            ->get();
    }
}
