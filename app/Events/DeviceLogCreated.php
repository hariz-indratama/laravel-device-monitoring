<?php

namespace App\Events;

use App\Models\DeviceLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeviceLogCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DeviceLog $log
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->log->device_id) {
            $channels[] = new PrivateChannel('device.' . $this->log->device_id);
        }

        if ($this->log->device?->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->log->device->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'device.log.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->log->id,
            'device_id'    => $this->log->device_id,
            'status'       => $this->log->status,
            'start_uptime' => $this->log->start_uptime?->toIso8601String(),
            'end_uptime'   => $this->log->end_uptime?->toIso8601String(),
            'logged_at'    => $this->log->logged_at?->toIso8601String(),
        ];
    }

    public function fail($exception = null): void
    {
        Log::error('[Broadcast] DeviceLogCreated failed', [
            'log_id' => $this->log->id,
            'error'  => $exception?->getMessage() ?? 'Unknown',
        ]);
    }
}
