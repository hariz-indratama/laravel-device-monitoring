<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;

class DevicePolicy
{
    /**
     * Determine if the user can view the device.
     */
    public function view(User $user, Device $device): bool
    {
        return $this->ownsDevice($user, $device);
    }

    /**
     * Determine if the user can update the device.
     */
    public function update(User $user, Device $device): bool
    {
        return $this->ownsDevice($user, $device);
    }

    /**
     * Determine if the user can delete the device.
     */
    public function delete(User $user, Device $device): bool
    {
        return $this->ownsDevice($user, $device);
    }

    /**
     * Determine if the user can toggle the device status.
     */
    public function toggle(User $user, Device $device): bool
    {
        return $this->ownsDevice($user, $device);
    }

    /**
     * Check if the user owns the device (directly or via outlet).
     */
    private function ownsDevice(User $user, Device $device): bool
    {
        return $user->id === $device->user_id
            || $user->id === $device->outlet?->user_id;
    }
}
