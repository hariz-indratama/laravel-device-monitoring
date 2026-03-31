<?php

namespace App\Enums;

enum DeviceStatus: string
{
    case Online     = 'online';
    case Offline    = 'offline';
    case Warning    = 'warning';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Online      => 'Online',
            self::Offline     => 'Offline',
            self::Warning     => 'Warning',
            self::Maintenance => 'Maintenance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Online      => 'emerald',
            self::Offline     => 'red',
            self::Warning     => 'amber',
            self::Maintenance => 'slate',
        };
    }
}
