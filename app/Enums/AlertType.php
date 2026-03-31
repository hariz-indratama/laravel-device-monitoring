<?php

namespace App\Enums;

enum AlertType: string
{
    case TemperatureHigh    = 'temperature_high';
    case TemperatureLow     = 'temperature_low';
    case DeviceOffline       = 'device_offline';
    case DeviceOnline        = 'device_online';
    case BatteryLow          = 'battery_low';
    case SensorMalfunction    = 'sensor_malfunction';
    case HeartbeatMissed     = 'heartbeat_missed';

    public function label(): string
    {
        return match ($this) {
            self::TemperatureHigh    => 'Suhu Tinggi',
            self::TemperatureLow     => 'Suhu Rendah',
            self::DeviceOffline      => 'Device Offline',
            self::DeviceOnline       => 'Device Online Kembali',
            self::BatteryLow         => 'Baterai Lemah',
            self::SensorMalfunction   => 'Sensor Bermasalah',
            self::HeartbeatMissed    => 'Heartbeat Terlewat',
        };
    }
}
