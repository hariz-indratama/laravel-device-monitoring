<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'address'       => $this->address,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'kota'          => $this->kota,
            'provinsi'      => $this->provinsi,
            'kode_pos'      => $this->kode_pos,
            'user_id'       => $this->user_id,
            'devices_count' => $this->whenCounted('devices'),
            'devices'       => $this->relationLoaded('devices')
                ? collect($this->devices)->map(fn($d) => (new DeviceResource($d))->jsonSerialize())->all()
                : [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
