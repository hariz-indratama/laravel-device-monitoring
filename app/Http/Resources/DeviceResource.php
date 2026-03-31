<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'serial_number'     => $this->serial_number,
            'type'              => $this->type,
            'location'          => $this->location,
            'status'            => $this->status->value,
            'status_label'      => $this->status->label(),
            'status_color'      => $this->status->color(),
            'is_on'             => $this->isOn(),
            'is_off'            => $this->isOff(),
            'uptime_started_at' => $this->uptime_started_at?->toIso8601String(),
            'assigned_at'       => $this->assigned_at?->toIso8601String(),
            'uptime_seconds'    => $this->getUptimeInSeconds(),
            'formatted_uptime'  => $this->getFormattedUptime(),
            'temperature_min'    => $this->temperature_min,
            'temperature_max'    => $this->temperature_max,
            'battery_threshold'  => $this->battery_threshold,
            'heartbeat_interval' => $this->heartbeat_interval,
            'outlet_id'         => $this->outlet_id,
            'outlet'            => $this->whenLoaded('outlet'),
            'outlet_name'       => $this->whenLoaded('outlet', fn() => $this->outlet?->name),
            'outlet_location'   => $this->whenLoaded('outlet', fn() => $this->outlet?->address ?? $this->outlet?->name),
            'latest_log'        => $this->whenLoaded('latestLog', fn() => new DeviceLogResource($this->latestLog)),
            'logs'              => DeviceLogResource::collection($this->whenLoaded('logs')),
            'alerts'            => DeviceAlertResource::collection($this->whenLoaded('alerts')),
            'active_alerts'     => $this->whenCounted('unresolvedAlerts'),
            'created_at'        => $this->created_at->toIso8601String(),
            'updated_at'        => $this->updated_at->toIso8601String(),
        ];
    }
}
