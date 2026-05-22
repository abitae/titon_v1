<?php

namespace App\Enums;

enum OrderType
{
    case Purchase;
    case Service;

    public function value(): string
    {
        return match ($this) {
            self::Purchase => 'compra',
            self::Service => 'servicio',
        };
    }

    public function suffix(): string
    {
        return match ($this) {
            self::Purchase => 'OC',
            self::Service => 'OS',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Orden de compra',
            self::Service => 'Orden de servicio',
        };
    }
}
