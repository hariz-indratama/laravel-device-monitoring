<?php

namespace Database\Factories;

use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Models\Device;
use App\Models\DeviceAlert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceAlert>
 */
class DeviceAlertFactory extends Factory
{
    protected $model = DeviceAlert::class;

    public function definition(): array
    {
        return [
            'device_id'   => Device::factory(),
            'type'        => fake()->randomElement(AlertType::cases()),
            'message'     => fake()->sentence(8),
            'severity'   => fake()->randomElement(AlertSeverity::cases()),
            'data'       => null,
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }

    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => AlertSeverity::Info,
        ]);
    }

    public function warning(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => AlertSeverity::Warning,
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => AlertSeverity::Critical,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved_at' => now()->subMinutes(fake()->numberBetween(5, 120)),
            'resolved_by' => User::factory(),
        ]);
    }

    public function unresolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }

    public function temperatureHigh(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'    => AlertType::TemperatureHigh,
            'message' => 'Suhu超出阈值: ' . fake()->randomFloat(1, 32, 50) . '°C',
            'severity' => AlertSeverity::Warning,
        ]);
    }

    public function temperatureLow(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'    => AlertType::TemperatureLow,
            'message' => 'Suhu di bawah阈值: ' . fake()->randomFloat(1, -10, 15) . '°C',
            'severity' => AlertSeverity::Warning,
        ]);
    }

    public function batteryLow(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'    => AlertType::BatteryLow,
            'message' => 'Baterai lemah: ' . fake()->numberBetween(1, 15) . '%',
            'severity' => AlertSeverity::Warning,
        ]);
    }

    public function deviceOffline(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'    => AlertType::DeviceOffline,
            'message' => 'Device offline - tidak ada response',
            'severity' => AlertSeverity::Critical,
        ]);
    }
}
