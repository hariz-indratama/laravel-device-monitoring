<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'device_id'   => $this->device_id,
            'type'        => $this->type->value,
            'type_label'  => $this->type->label(),
            'message'     => $this->message,
            'severity'    => $this->severity->value,
            'severity_label' => $this->severity->label(),
            'severity_color' => $this->severity->color(),
            'data'        => $this->data,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolved_by' => $this->whenLoaded('resolver', fn() => [
                'id'   => $this->resolver->id,
                'name' => $this->resolver->name,
            ]),
            'created_at'  => $this->created_at->toIso8601String(),
            'device'      => $this->whenLoaded('device', fn() => [
                'id'   => $this->device->id,
                'name' => $this->device->name,
            ]),
        ];
    }
}
