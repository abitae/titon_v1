<?php

namespace App\Enums;

enum SupplierStatus
{
    case Active;
    case Inactive;

    public function value(): string
    {
        return match ($this) {
            self::Active => 'active',
            self::Inactive => 'inactive',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $status): string => $status->value(),
            self::cases(),
        );
    }
}
