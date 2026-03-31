<?php

namespace App\Policies;

use App\Models\DeviceAlert;
use App\Models\User;

class DeviceAlertPolicy
{
    /**
     * Determine if the user can resolve the alert.
     */
    public function resolve(User $user, DeviceAlert $alert): bool
    {
        $device = $alert->device;

        if (! $device) {
            return false;
        }

        return $user->id === $device->user_id
            || $user->id === $device->outlet?->user_id;
    }
}
