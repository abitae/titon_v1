<?php

namespace App\Enums;

enum FleetSparePartStatus
{
    case Active;
    case Inactive;

    public function value(): string
    {
        return match ($this) {
            self::Active => 'activo',
            self::Inactive => 'inactivo',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
        };
    }
}
