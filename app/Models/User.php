<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::Owner;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    /** @return HasMany<Outlet> */
    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    /** @return HasMany<Device> */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
