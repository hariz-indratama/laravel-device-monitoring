<?php

namespace App\Events;

use App\Models\DeviceAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeviceAlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DeviceAlert $alert
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->alert->device_id) {
            $channels[] = new PrivateChannel('device.' . $this->alert->device_id);
        }

        if ($this->alert->device?->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->alert->device->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'device.alert.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->alert->id,
            'device_id' => $this->alert->device_id,
            'type'      => $this->alert->type->value,
            'type_label'=> $this->alert->type->label(),
            'message'   => $this->alert->message,
            'severity'  => $this->alert->severity->value,
            'data'      => $this->alert->data,
            'created_at'=> $this->alert->created_at?->toIso8601String(),
        ];
    }

    public function fail($exception = null): void
    {
        Log::error('[Broadcast] DeviceAlertCreated failed', [
            'alert_id' => $this->alert->id,
            'error'    => $exception?->getMessage() ?? 'Unknown',
        ]);
    }
}
