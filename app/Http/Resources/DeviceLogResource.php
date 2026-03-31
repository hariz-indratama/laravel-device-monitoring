<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'device_id'    => $this->device_id,
            'status'       => $this->status,
            'start_uptime' => $this->start_uptime?->toIso8601String(),
            'end_uptime'   => $this->end_uptime?->toIso8601String(),
            'logged_at'    => $this->logged_at?->toIso8601String(),
            'device'       => $this->whenLoaded('device', fn() => [
                'id'   => $this->device->id,
                'name' => $this->device->name,
            ]),
        ];
    }
}
