<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\DeviceHeartbeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceHeartbeat>
 */
class DeviceHeartbeatFactory extends Factory
{
    protected $model = DeviceHeartbeat::class;

    public function definition(): array
    {
        return [
            'device_id'        => Device::factory(),
            'last_seen_at'    => fake()->dateTimeBetween('-1 hour', 'now'),
            'missed_count'    => 0,
            'interval_seconds'=> fake()->randomElement([60, 120, 300, 600]),
        ];
    }

    public function stale(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => now()->subMinutes(fake()->numberBetween(10, 60)),
        ]);
    }

    public function fresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => now(),
            'missed_count' => 0,
        ]);
    }

    public function withMissedCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'missed_count' => $count,
        ]);
    }
}
