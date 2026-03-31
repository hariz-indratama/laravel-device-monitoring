<?php

namespace App\Console\Commands;

use App\Enums\DeviceStatus;
use App\Models\Device;
use App\Models\DeviceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Test command for simulating real device data broadcast.
 *
 * Usage:
 *   php artisan device:test-broadcast           # Interactive mode
 *   php artisan device:test-broadcast --loop   # Continuous broadcast every 30s
 *   php artisan device:test-broadcast --serial=DEV-XXX123  # Use existing device
 *   php artisan device:test-broadcast --create  # Create new device + run
 *   php artisan device:test-broadcast --warm    # Create device with historical logs
 */
class TestDeviceBroadcast extends Command
{
    protected $signature = '
        device:test-broadcast
        {--loop : Continuously broadcast data every 30 seconds}
        {--serial= : Use specific device by serial number}
        {--create : Create a new test device}
        {--warm : Create device with 24h historical logs}
        {--interval=30 : Broadcast interval in seconds (for --loop)}
        {--url= : Override base URL (default: http://localhost)}
        {--port=8000 : Server port (default: 8000)}
    ';

    protected $description = 'Simulate real device data broadcast to test the API ingestion & broadcast reverb';

    private ?Device $device = null;

    public function handle(): int
    {
        $baseUrl = rtrim(
            rtrim($this->option('url') ?? 'http://127.0.0.1', '/') . ':' . $this->option('port'),
            ':'
        );
        $interval = (int) $this->option('interval');

        if (!$this->resolveDevice()) {
            return Command::FAILURE;
        }

        if ($this->option('warm')) {
            $this->warmHistory();
        }

        if ($this->option('loop')) {
            $this->broadcastLoop($baseUrl, $interval);
        } else {
            $this->broadcastOnce($baseUrl);
        }

        return Command::SUCCESS;
    }

    private function resolveDevice(): bool
    {
        if ($serial = $this->option('serial')) {
            $this->device = Device::where('serial_number', $serial)->first();

            if (!$this->device) {
                $this->error("Device with serial [{$serial}] not found.");
                $this->info('Available devices:');
                Device::query()->with('outlet')->limit(10)->get()->each(function (Device $d) {
                    $this->line("  - {$d->serial_number}  [{$d->name}]  status={$d->status->value}");
                });
                $this->newLine();
                $this->info('Tip: Use --create to create a new test device:');
                $this->line("  php artisan device:test-broadcast --create");

                return false;
            }

            $this->info("Using existing device: {$this->device->serial_number} - {$this->device->name}");

            return true;
        }

        if ($this->option('create') || $this->option('warm')) {
            $this->device = $this->createDevice();

            return true;
        }

        // Interactive: pick from list
        $devices = Device::query()
            ->with('outlet')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        if ($devices->isEmpty()) {
            $this->warn('No devices found. Creating one now...');
            $this->device = $this->createDevice();

            return true;
        }

        $this->info('Available devices:');
        $devices->each(function (Device $d, int $i) {
            $this->line(sprintf(
                "  [%d] %s  %-30s  %-12s  (%s)",
                $i + 1,
                $d->serial_number,
                $d->name,
                $d->status->value,
                $d->outlet?->name ?? 'no outlet'
            ));
        });

        $choice = $this->ask('Select device number', '1');
        $index = ((int) $choice) - 1;

        if (!isset($devices[$index])) {
            $this->error('Invalid selection.');

            return false;
        }

        $this->device = $devices[$index];
        $this->info("Selected: {$this->device->serial_number}");

        return true;
    }

    private function createDevice(): Device
    {
        $user = \App\Models\User::first();
        if (!$user) {
            $user = \App\Models\User::factory()->create();
        }

        $outlet = \App\Models\Outlet::factory()
            ->for($user)
            ->create();

        $serial = 'DEV-' . strtoupper(bin2hex(random_bytes(3)));

        $device = Device::factory()
            ->for($outlet, 'outlet')
            ->for($user, 'user')
            ->create([
                'serial_number'   => $serial,
                'name'            => 'Test Sensor ' . substr($serial, -5),
                'type'            => 'combo_sensor',
                'location'        => 'Ruang Server Test',
                'status'          => DeviceStatus::Online,
                'temperature_min' => 18,
                'temperature_max' => 30,
                'battery_threshold' => 20,
                'heartbeat_interval' => 60,
            ]);

        $this->info("✓ Created device: {$device->serial_number} - {$device->name}");
        $this->table(
            ['Field', 'Value'],
            [
                ['Serial Number', $device->serial_number],
                ['API Endpoint', "POST {$this->option('url')}:{$this->option('port')}/api/v1/devices/{$device->serial_number}/data"],
                ['Status', $device->status->value],
                ['Thresholds', "{$device->temperature_min}°C – {$device->temperature_max}°C"],
                ['Battery Limit', "{$device->battery_threshold}%"],
            ]
        );

        return $device;
    }

    private function warmHistory(): void
    {
        $this->info('Warming up with 24h historical logs...');

        $logsCount = $this->option('loop') ? 100 : 50;
        $logs = DeviceLog::factory()
            ->count($logsCount)
            ->make([
                'device_id' => $this->device->id,
            ]);

        foreach ($logs as $log) {
            $log->logged_at = fake()->dateTimeBetween('-24 hours', '-5 seconds');
            $log->save();
        }

        $this->info("✓ Generated {$logsCount} historical logs.");
    }

    private function broadcastOnce(string $baseUrl): void
    {
        $payload = $this->buildPayload();
        $this->sendBroadcast($baseUrl, $payload);
    }

    private function broadcastLoop(string $baseUrl, int $interval): void
    {
        $bar = $this->output->createProgressBar();
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s%');
        $bar->setMaxTicks(PHP_INT_MAX);
        $bar->start();

        $iteration = 0;

        while (true) {
            $iteration++;
            $payload = $this->buildPayload();
            $success = $this->sendBroadcast($baseUrl, $payload);

            $bar->setMessage("Iteration #{$iteration} — {$this->device->serial_number}", 'iteration');

            if (!$success) {
                $this->error("\n✗ Broadcast failed on iteration #{$iteration}");
            }

            sleep($interval);
            $bar->advance();
        }
    }

    private function buildPayload(): array
    {
        // 80% on, 20% off — simulates realistic usage
        $status = mt_rand(1, 100) <= 80 ? 'on' : 'off';

        $payload = [
            'status'    => $status,
            'logged_at' => now()->toIso8601String(),
        ];

        if ($status === 'on') {
            $payload['start_uptime'] = now()->subMinutes(mt_rand(1, 120))->toIso8601String();
        } else {
            $payload['start_uptime'] = now()->subMinutes(mt_rand(30, 240))->toIso8601String();
            $payload['end_uptime']   = now()->toIso8601String();
        }

        return $payload;
    }

    private function sendBroadcast(string $baseUrl, array $payload): bool
    {
        $endpoint = "{$baseUrl}/api/v1/devices/{$this->device->serial_number}/data";

        $this->line('');
        $this->info("→ Broadcasting to: {$endpoint}");

        try {
            $response = Http::timeout(10)->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();

                $this->info('✓ Broadcast successful');
                $this->table(
                    ['Field', 'Value'],
                    collect($payload)->map(fn ($v, $k) => [$k, is_float($v) ? round($v, 2) : $v])->values()->toArray()
                );

                if (!empty($data['alert'])) {
                    $this->warn("⚠ Alert triggered: [{$data['alert']['type']}] {$data['alert']['message']}");
                } else {
                    $this->comment('  No alert triggered.');
                }

                Log::info('[TestBroadcast] Device data ingested', [
                    'serial' => $this->device->serial_number,
                    'payload'=> $payload,
                    'response' => $data,
                ]);

                return true;
            } else {
                $this->error("✗ HTTP {$response->status()}: {$response->body()}");
                return false;
            }
        } catch (\Exception $e) {
            $this->error("✗ Connection error: {$e->getMessage()}");
            $this->warn('  Make sure the Laravel server is running:');
            $this->warn("  php artisan serve --port={$this->option('port')}");
            return false;
        }
    }
}
