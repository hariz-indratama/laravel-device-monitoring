<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeviceStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Device $device
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        // Always broadcast to device channel (for real-time dashboard updates)
        $channels[] = new PrivateChannel('device.' . $this->device->id);

        // Only add user channel if user_id is set
        if ($this->device->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->device->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'device.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'               => $this->device->id,
            'name'             => $this->device->name,
            'serial_number'    => $this->device->serial_number,
            'status'           => $this->device->status->value,
            'uptime_started_at'=> $this->device->uptime_started_at?->toIso8601String(),
            'formatted_uptime' => $this->device->getFormattedUptime(),
            'is_on'            => $this->device->isOn(),
            'is_off'           => $this->device->isOff(),
            'outlet_id'        => $this->device->outlet_id,
            'updated_at'       => $this->device->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Prevent broadcast failures from crashing the ingestion API.
     */
    public function fail($exception = null): void
    {
        Log::error('[Broadcast] DeviceStatusUpdated failed', [
            'device_id' => $this->device->id,
            'error'     => $exception?->getMessage() ?? 'Unknown',
        ]);
    }
}

