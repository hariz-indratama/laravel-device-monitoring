<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceHeartbeat extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'last_seen_at',
        'missed_count',
        'interval_seconds',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at'    => 'datetime',
            'missed_count'    => 'integer',
            'interval_seconds' => 'integer',
        ];
    }

    /** @return BelongsTo<Device, DeviceHeartbeat> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function isStale(int $thresholdMinutes = 5): bool
    {
        return $this->last_seen_at->addMinutes($thresholdMinutes)->isPast();
    }

    public function markSeen(): void
    {
        $this->update(['last_seen_at' => now(), 'missed_count' => 0]);
    }

    public function incrementMissed(): void
    {
        $this->increment('missed_count');
    }
}
