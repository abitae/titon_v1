<?php

namespace App\Enums;

enum FleetSparePartMovementDirection
{
    case Inbound;
    case Outbound;

    public function value(): string
    {
        return match ($this) {
            self::Inbound => 'entrada',
            self::Outbound => 'salida',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Inbound => 'Entrada',
            self::Outbound => 'Salida',
        };
    }
}
