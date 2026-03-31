<?php

namespace App\Models;

use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAlert extends Model
{
    use HasFactory;

    public function scopeForUser(Builder $query, $user): Builder
    {
        return $query->whereHas('device', fn($q) => $q->forUser($user));
    }

    protected $fillable = [
        'device_id',
        'type',
        'message',
        'severity',
        'data',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'type'       => AlertType::class,
            'severity'   => AlertSeverity::class,
            'data'       => 'array',
            'resolved_at'=> 'datetime',
        ];
    }

    /** @return BelongsTo<Device, DeviceAlert> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /** @return BelongsTo<User, DeviceAlert> */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function isCritical(): bool
    {
        return $this->severity === AlertSeverity::Critical;
    }
}
