<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    public function scopeForUser(Builder $query, $user): Builder
    {
        $userId = is_object($user) ? ($user->id ?? null) : $user;

        if ($userId === null) {
            return $query->whereRaw('1 = 0'); // no results for unauthenticated
        }

        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId);
            $q->orWhereHas('outlet', fn($sq) => $sq->where('user_id', $userId));
        });
    }

    protected $fillable = [
        'outlet_id',
        'user_id',
        'name',
        'serial_number',
        'type',
        'location',
        'status',
        'uptime_started_at',
        'assigned_at',
        'temperature_min',
        'temperature_max',
        'battery_threshold',
        'heartbeat_interval',
    ];

    protected function casts(): array
    {
        return [
            'status'             => DeviceStatus::class,
            'type'               => DeviceType::class,
            'uptime_started_at'  => 'datetime',
            'assigned_at'        => 'datetime',
            'temperature_min'    => 'float',
            'temperature_max'    => 'float',
            'battery_threshold'  => 'integer',
            'heartbeat_interval' => 'integer',
        ];
    }

    /** @return BelongsTo<Outlet, Device> */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /** @return BelongsTo<User, Device> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<DeviceLog> */
    public function logs(): HasMany
    {
        return $this->hasMany(DeviceLog::class)->orderByDesc('logged_at');
    }

    /** @return HasMany<DeviceAlert> */
    public function alerts(): HasMany
    {
        return $this->hasMany(DeviceAlert::class)->orderByDesc('created_at');
    }

    /** @return HasMany<DeviceAlert> */
    public function unresolvedAlerts(): HasMany
    {
        return $this->hasMany(DeviceAlert::class)->whereNull('resolved_at');
    }

    /** @return HasOne<DeviceHeartbeat> */
    public function heartbeat(): HasOne
    {
        return $this->hasOne(DeviceHeartbeat::class);
    }

    /** @return HasOne<DeviceLog> */
    public function latestLog(): HasOne
    {
        return $this->hasOne(DeviceLog::class)->latestOfMany('logged_at');
    }

    public function isOnline(): bool
    {
        return $this->status === DeviceStatus::Online;
    }

    public function isOffline(): bool
    {
        return $this->status === DeviceStatus::Offline;
    }

    /**
     * Alias for isOnline() — used by frontend broadcast logic.
     */
    public function isOn(): bool
    {
        return $this->isOnline();
    }

    /**
     * Alias for isOffline() — used by frontend broadcast logic.
     */
    public function isOff(): bool
    {
        return $this->isOffline();
    }

    public function getUptimeInSeconds(): ?int
    {
        if (!$this->uptime_started_at || !$this->isOnline()) {
            return null;
        }
        return (int) $this->uptime_started_at->diffInSeconds(now());
    }

    public function getFormattedUptime(): string
    {
        $seconds = $this->getUptimeInSeconds();
        if ($seconds === null) {
            return '—';
        }

        $days  = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        $mins  = (int) floor(($seconds % 3600) / 60);
        $secs  = $seconds % 60;

        if ($days > 0) {
            return "{$days}d {$hours}h {$mins}m";
        }
        if ($hours > 0) {
            return "{$hours}h {$mins}m {$secs}s";
        }
        if ($mins > 0) {
            return "{$mins}m {$secs}s";
        }
        return "{$secs}s";
    }

    public function markOnline(): void
    {
        $this->update([
            'status'            => DeviceStatus::Online,
            'uptime_started_at' => now(),
        ]);
    }

    public function markOffline(): void
    {
        $this->update([
            'status' => DeviceStatus::Offline,
        ]);
    }
}
