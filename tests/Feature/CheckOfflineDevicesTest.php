<?php

use App\Enums\DeviceStatus;
use App\Jobs\CheckOfflineDevices;
use App\Models\Device;
use App\Models\DeviceHeartbeat;
use App\Models\Outlet;
use App\Models\User;

test('CheckOfflineDevices marks stale devices as offline', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->online()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    // Create a stale heartbeat (last seen 10 minutes ago)
    DeviceHeartbeat::create([
        'device_id'        => $device->id,
        'last_seen_at'     => now()->subMinutes(10),
        'missed_count'     => 0,
        'interval_seconds' => 300,
    ]);

    // Run the job
    (new CheckOfflineDevices)->handle(new \App\Services\NotificationService);

    $device->refresh();
    expect($device->status)->toBe(DeviceStatus::Offline);

    $this->assertDatabaseHas('device_alerts', [
        'device_id' => $device->id,
        'type'      => 'heartbeat_missed',
    ]);
});

test('CheckOfflineDevices does not affect fresh devices', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->online()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    // Create a fresh heartbeat (last seen 1 minute ago)
    DeviceHeartbeat::create([
        'device_id'        => $device->id,
        'last_seen_at'     => now()->subMinute(),
        'missed_count'     => 0,
        'interval_seconds' => 300,
    ]);

    (new CheckOfflineDevices)->handle(new \App\Services\NotificationService);

    $device->refresh();
    expect($device->status)->toBe(DeviceStatus::Online);
});

test('CheckOfflineDevices skips already offline devices', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->offline()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    DeviceHeartbeat::create([
        'device_id'        => $device->id,
        'last_seen_at'     => now()->subMinutes(10),
        'missed_count'     => 0,
        'interval_seconds' => 300,
    ]);

    (new CheckOfflineDevices)->handle(new \App\Services\NotificationService);

    // Should not create an alert for already offline device
    $this->assertDatabaseMissing('device_alerts', [
        'device_id' => $device->id,
    ]);
});
