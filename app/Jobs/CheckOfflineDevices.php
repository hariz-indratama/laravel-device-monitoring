<?php

namespace App\Jobs;

use App\Enums\DeviceStatus;
use App\Events\DeviceStatusUpdated;
use App\Models\Device;
use App\Models\DeviceAlert;
use App\Models\DeviceHeartbeat;
use App\Enums\AlertType;
use App\Enums\AlertSeverity;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckOfflineDevices implements ShouldQueue
{
    use Queueable;

    public function handle(NotificationService $notification): void
    {
        $thresholdMinutes = (int) config('device-monitoring.offline_threshold_minutes', 5);

        // Cek semua device yang punya heartbeat
        $heartbeats = DeviceHeartbeat::with('device')->get();

        foreach ($heartbeats as $heartbeat) {
            if ($heartbeat->isStale($thresholdMinutes)) {
                $device = $heartbeat->device;

                // Skip kalau sudah offline atau maintenance
                if (in_array($device->status, [DeviceStatus::Offline, DeviceStatus::Maintenance])) {
                    continue;
                }

                // Increment missed count
                $heartbeat->incrementMissed();

                // Update status
                $device->update(['status' => DeviceStatus::Offline]);

                // Buat alert
                $alert = DeviceAlert::create([
                    'device_id' => $device->id,
                    'type'      => AlertType::HeartbeatMissed,
                    'message'   => "Device offline (missed {$heartbeat->missed_count} heartbeats)",
                    'severity'  => AlertSeverity::Critical,
                    'data'      => ['missed_count' => $heartbeat->missed_count],
                ]);

                // Broadcast
                DeviceStatusUpdated::dispatch($device->fresh());

                // Kirim notifikasi
                $notification->sendOfflineNotification($device);

                Log::warning("Device offline detected: {$device->name}", [
                    'device_id'     => $device->id,
                    'missed_count' => $heartbeat->missed_count,
                ]);
            }
        }
    }
}
