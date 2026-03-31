<?php

use App\Models\Device;
use App\Models\Outlet;
use App\Models\User;

test('user can view their own devices list', function () {
    $user = User::factory()->create(['role' => 'owner']);
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    Device::factory()->count(3)->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->actingAs($user)->get(route('devices.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Devices/Index')
        ->has('devices.data', 3)
    );
});

test('user cannot view another user\'s device', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $other = User::factory()->create(['role' => 'owner']);
    $outlet = Outlet::factory()->create(['user_id' => $other->id]);
    $device = Device::factory()->create([
        'user_id'   => $other->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->actingAs($owner)->get(route('devices.show', $device));

    $response->assertStatus(403);
});

test('user can create a device', function () {
    $user = User::factory()->create(['role' => 'owner']);
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('devices.store'), [
        'name'          => 'Test Console',
        'serial_number' => 'DEV-TEST-001',
        'type'          => 'game_console',
        'outlet_id'     => $outlet->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('devices', [
        'serial_number' => 'DEV-TEST-001',
        'user_id'       => $user->id,
    ]);
});

test('user can delete their own device', function () {
    $user = User::factory()->create(['role' => 'owner']);
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->actingAs($user)->delete(route('devices.destroy', $device));

    $response->assertRedirect(route('devices.index'));
    $this->assertDatabaseMissing('devices', ['id' => $device->id]);
});

test('user cannot delete another user\'s device', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $other = User::factory()->create(['role' => 'owner']);
    $outlet = Outlet::factory()->create(['user_id' => $other->id]);
    $device = Device::factory()->create([
        'user_id'   => $other->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->actingAs($owner)->delete(route('devices.destroy', $device));

    $response->assertStatus(403);
    $this->assertDatabaseHas('devices', ['id' => $device->id]);
});

test('user can toggle device status', function () {
    $user = User::factory()->create(['role' => 'owner']);
    $outlet = Outlet::factory()->create(['user_id' => $user->id]);
    $device = Device::factory()->offline()->create([
        'user_id'   => $user->id,
        'outlet_id' => $outlet->id,
    ]);

    $response = $this->actingAs($user)->patch(route('devices.toggle', $device));

    $response->assertRedirect();
    $this->assertDatabaseHas('devices', [
        'id'     => $device->id,
        'status' => 'online',
    ]);
});
