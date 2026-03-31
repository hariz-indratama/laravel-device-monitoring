<?php

use App\Models\Device;
use App\Models\Outlet;
use App\Models\User;

test('device ingestion creates log and returns success', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->offline()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->postJson("/api/v1/devices/{$device->serial_number}/data", [
        'status'    => 'on',
        'logged_at' => now()->toIso8601String(),
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('device_logs', [
        'device_id' => $device->id,
        'status'    => 'on',
    ]);
});

test('device ingestion updates device status to online', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->offline()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $this->postJson("/api/v1/devices/{$device->serial_number}/data", [
        'status' => 'on',
    ]);

    $device->refresh();
    expect($device->status->value)->toBe('online');
});

test('device ingestion rejects invalid status', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->postJson("/api/v1/devices/{$device->serial_number}/data", [
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422);
});

test('device ingestion returns 404 for unknown serial', function () {
    $response = $this->postJson('/api/v1/devices/UNKNOWN-SERIAL/data', [
        'status' => 'on',
    ]);

    $response->assertStatus(404);
});

test('device ingestion creates heartbeat entry', function () {
    $user = User::factory()->create();
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $this->postJson("/api/v1/devices/{$device->serial_number}/data", [
        'status' => 'on',
    ]);

    $this->assertDatabaseHas('device_heartbeats', [
        'device_id'   => $device->id,
        'missed_count' => 0,
    ]);
});
