<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\DeviceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceLog>
 */
class DeviceLogFactory extends Factory
{
    protected $model = DeviceLog::class;

    public function definition(): array
    {
        return [
            'device_id'    => Device::factory(),
            'status'       => fake()->randomElement(['on', 'off']),
            'start_uptime' => fake()->dateTimeBetween('-2 hours', '-1 hour'),
            'end_uptime'   => null,
            'logged_at'    => fake()->dateTimeBetween('-1 hour', 'now'),
        ];
    }

    /**
     * Device is powered on.
     */
    public function on(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'on',
        ]);
    }

    /**
     * Device is powered off.
     */
    public function off(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => 'off',
            'end_uptime' => fake()->dateTimeBetween('-30 minutes', 'now'),
        ]);
    }
}
