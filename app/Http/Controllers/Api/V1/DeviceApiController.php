<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IngestDeviceDataRequest;
use App\Http\Resources\DeviceLogResource;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use App\Models\DeviceAlert;
use App\Models\DeviceHeartbeat;
use App\Models\DeviceLog;
use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Enums\DeviceStatus;
use App\Events\DeviceStatusUpdated;
use App\Events\DeviceLogCreated;
use App\Services\DeviceIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceApiController extends Controller
{
    public function __construct(
        private DeviceIngestService $ingestService
    ) {}

    /**
     * Ingest data dari device IoT
     * POST /api/v1/devices/{id}/data
     */
    public function ingest(IngestDeviceDataRequest $request, string $serialNumber): JsonResponse
    {
        $device = Device::where('serial_number', $serialNumber)->firstOrFail();

        $validated = $request->validated();

        // Simpan log
        $log = $this->ingestService->storeLog($device, $validated);

        // Broadcast log ke frontend via WebSocket
        try {
            DeviceLogCreated::dispatch($log->fresh()->load('device'));
        } catch (\Throwable $e) {
            Log::warning('[Ingest] Log broadcast failed', [
                'log_id' => $log->id,
                'error'  => $e->getMessage(),
            ]);
        }

        // Update heartbeat
        $this->ingestService->touchHeartbeat($device);

        // Update device status & uptime based on payload
        $newStatus = $validated['status'] === 'on' ? DeviceStatus::Online : DeviceStatus::Offline;
        $shouldBroadcast = false;

        if ($device->status !== $newStatus) {
            $update = ['status' => $newStatus];

            if ($newStatus === DeviceStatus::Online) {
                $update['uptime_started_at'] = $validated['start_uptime']
                    ? \Carbon\Carbon::parse($validated['start_uptime'])
                    : now();
            } else {
                unset($update['uptime_started_at']);
            }

            $device->update($update);
            $shouldBroadcast = true;

            if ($shouldBroadcast) {
                try {
                    DeviceStatusUpdated::dispatch($device->fresh());
                } catch (\Throwable $e) {
                    Log::warning('[Ingest] Broadcast failed', [
                        'device_id' => $device->id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'log'     => (new DeviceLogResource($log))->jsonSerialize(),
        ]);
    }

    /**
     * Get device info ( Flutter Mobile )
     * GET /api/v1/devices/{serial_number}
     */
    public function show(string $serialNumber): JsonResponse
    {
        $device = Device::where('serial_number', $serialNumber)
            ->with('latestLog')
            ->firstOrFail();

        return response()->json([
            'device' => new DeviceResource($device),
        ]);
    }
}
