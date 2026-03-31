<?php

use App\Models\Device;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('device.{id}', function ($user, $id) {
    $device = Device::find($id);

    if (! $device) {
        return false;
    }

    // User adalah owner device atau owner outlet
    return $user->id === $device->user_id
        || $user->id === $device->outlet?->user_id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return $user->id === (int) $id;
});
