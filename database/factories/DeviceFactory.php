<?php

namespace Database\Factories;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\Device;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'outlet_id'          => Outlet::factory(),
            'user_id'            => User::factory(),
            'name'               => fake()->randomElement(['PS5 Station', 'Arcade Machine', 'Game Console']) . ' ' . fake()->numberBetween(1, 99),
            'serial_number'      => 'DEV-' . strtoupper(fake()->unique()->bothify('???###')),
            'type'               => fake()->randomElement(DeviceType::cases()),
            'location'           => fake()->randomElement(['Lantai 1 Area A', 'Lantai 1 Area B', 'Arcade Zone', 'VIP Room', 'Lantai 2']),
            'status'             => DeviceStatus::Offline,
            'temperature_min'    => fake()->randomFloat(1, 15, 20),
            'temperature_max'    => fake()->randomFloat(1, 25, 35),
            'battery_threshold'  => fake()->numberBetween(10, 30),
            'heartbeat_interval' => fake()->randomElement([60, 120, 300, 600]),
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'            => DeviceStatus::Online,
            'uptime_started_at' => now()->subMinutes(fake()->numberBetween(5, 480)),
        ]);
    }

    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeviceStatus::Offline,
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeviceStatus::Warning,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeviceStatus::Maintenance,
        ]);
    }

    public function gameConsole(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DeviceType::GameConsole,
        ]);
    }

    public function arcade(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DeviceType::Arcade,
        ]);
    }
}
