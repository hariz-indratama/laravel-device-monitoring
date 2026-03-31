<?php

namespace App\Services;

use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Events\DeviceAlertCreated;
use App\Models\Device;
use App\Models\DeviceAlert;
use App\Models\DeviceHeartbeat;
use App\Models\DeviceLog;
use Illuminate\Support\Facades\Log;

class DeviceIngestService
{
    public function storeLog(Device $device, array $data): DeviceLog
    {
        return DeviceLog::create([
            'device_id'    => $device->id,
            'status'       => $data['status'] ?? 'on',
            'start_uptime' => isset($data['start_uptime']) ? \Carbon\Carbon::parse($data['start_uptime']) : null,
            'end_uptime'   => isset($data['end_uptime']) ? \Carbon\Carbon::parse($data['end_uptime']) : null,
            'logged_at'    => $data['logged_at'] ?? now(),
        ]);
    }

    public function touchHeartbeat(Device $device): DeviceHeartbeat
    {
        return DeviceHeartbeat::updateOrCreate(
            ['device_id' => $device->id],
            [
                'last_seen_at'    => now(),
                'missed_count'   => 0,
                'interval_seconds'=> $device->heartbeat_interval,
            ]
        );
    }

    public function createAlert(
        Device $device,
        AlertType $type,
        string $message,
        AlertSeverity $severity,
        array $data = []
    ): DeviceAlert {
        $alert = DeviceAlert::create([
            'device_id' => $device->id,
            'type'      => $type,
            'message'   => $message,
            'severity'  => $severity,
            'data'      => $data,
        ]);

        // Load the device relation so broadcast channel has user_id
        $alert->load('device');

        try {
            DeviceAlertCreated::dispatch($alert->fresh());
        } catch (\Throwable $e) {
            Log::warning('[Ingest] Alert broadcast failed', [
                'alert_id' => $alert->id,
                'error'    => $e->getMessage(),
            ]);
        }

        return $alert;
    }
}
