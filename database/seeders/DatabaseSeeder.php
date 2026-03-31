<?php

namespace Database\Seeders;

use App\Enums\DeviceType;
use App\Models\Device;
use App\Models\DeviceHeartbeat;
use App\Models\DeviceLog;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create owner user
        $owner = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@devicemonitor.id',
            'password' => 'password',
            'role'     => 'owner',
        ]);

        // Create outlets
        $outlets = collect([
            ['name' => 'Gedung Server A', 'address' => 'Jl. Sudirman No. 1, Jakarta', 'phone' => '021-123456'],
            ['name' => 'Gudang Utama',   'address' => 'Jl. Gatot Subroto No. 5, Jakarta', 'phone' => '021-654321'],
        ])->map(fn($d) => $owner->outlets()->create($d));

        // Create devices
        $devices = collect([
            ['name' => 'PS5 Station 1',    'serial_number' => 'DEV-001', 'outlet_id' => $outlets[0]->id, 'location' => 'Lantai 1 Area A'],
            ['name' => 'PS5 Station 2',    'serial_number' => 'DEV-002', 'outlet_id' => $outlets[0]->id, 'location' => 'Lantai 1 Area B'],
            ['name' => 'Arcade Machine 1', 'serial_number' => 'DEV-003', 'outlet_id' => $outlets[1]->id, 'location' => 'Arcade Zone'],
            ['name' => 'PS5 Station 3',    'serial_number' => 'DEV-004', 'outlet_id' => $outlets[1]->id, 'location' => 'Lantai 2'],
        ])->map(fn($d) => $owner->devices()->create(array_merge($d, [
            'type'               => $d['serial_number'] === 'DEV-003' ? DeviceType::Arcade : DeviceType::GameConsole,
            'status'             => 'online',
            'uptime_started_at'  => now()->subHours(rand(1, 12)),
            'temperature_min'    => 18,
            'temperature_max'    => 25,
            'battery_threshold'  => 20,
            'heartbeat_interval' => 300,
        ])));

        // Seed heartbeat & sample logs for each device
        foreach ($devices as $device) {
            DeviceHeartbeat::create([
                'device_id'        => $device->id,
                'last_seen_at'     => now(),
                'missed_count'     => 0,
                'interval_seconds' => 300,
            ]);

            // 24 sample logs (1 per hour) — using current schema columns
            for ($i = 0; $i < 24; $i++) {
                DeviceLog::create([
                    'device_id'    => $device->id,
                    'status'       => $i % 8 === 0 ? 'off' : 'on', // ~12.5% off periods
                    'start_uptime' => now()->subHours(24 - $i),
                    'end_uptime'   => $i % 8 === 0 ? now()->subHours(24 - $i)->addMinutes(30) : null,
                    'logged_at'    => now()->subHours(24 - $i),
                ]);
            }
        }
    }
}
