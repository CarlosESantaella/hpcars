<?php

namespace App\Enums;

enum NotificationType: string
{
    case Itv = 'itv';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Itv => 'ITV',
            self::Maintenance => 'Mantenimiento',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Itv => 'amber',
            self::Maintenance => 'blue',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Itv => 'exclamation-triangle',
            self::Maintenance => 'wrench-screwdriver',
        };
    }
}
