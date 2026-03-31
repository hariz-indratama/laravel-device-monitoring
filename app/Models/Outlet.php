<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outlet extends Model
{
    use HasFactory;

    public function scopeForUser(Builder $query, $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    protected $fillable = ['name', 'address', 'phone', 'email', 'kota', 'kecamatan', 'desa', 'provinsi', 'kode_pos', 'user_id'];

    /** @return BelongsTo<User, Outlet> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<Device> */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
