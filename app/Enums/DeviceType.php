<?php

namespace App\Enums;

enum DeviceType: string
{
    case GameConsole = 'game_console';
    case Arcade     = 'arcade';

    public function label(): string
    {
        return match ($this) {
            self::GameConsole => 'Game Console',
            self::Arcade     => 'Arcade',
        };
    }
}
