<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Free = 'free';
    case Rented = 'rented';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Libre',
            self::Rented => 'Alquilado',
            self::Maintenance => 'Mantenimiento',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Free => 'green',
            self::Rented => 'yellow',
            self::Maintenance => 'red',
        };
    }
}
