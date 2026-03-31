<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function scopeForUser(Builder $query, $user): Builder
    {
        return $query->whereHas('device', fn($q) => $q->forUser($user));
    }

    protected $fillable = [
        'device_id',
        'status',
        'start_uptime',
        'end_uptime',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => 'string',
            'start_uptime' => 'datetime',
            'end_uptime'   => 'datetime',
            'logged_at'    => 'datetime',
        ];
    }

    /** @return BelongsTo<Device, DeviceLog> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
