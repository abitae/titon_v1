<?php

namespace App\Enums;

enum FleetEquipmentOperationalStatus
{
    case Operational;
    case InMaintenance;
    case Inactive;
    case Broken;
    case Decommissioned;

    public function value(): string
    {
        return match ($this) {
            self::Operational => 'operativo',
            self::InMaintenance => 'en_mantenimiento',
            self::Inactive => 'inactivo',
            self::Broken => 'averiado',
            self::Decommissioned => 'de_baja',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Operational => 'Operativo',
            self::InMaintenance => 'En mantenimiento',
            self::Inactive => 'Inactivo',
            self::Broken => 'Averiado',
            self::Decommissioned => 'De baja',
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

    public static function fromValue(string $value): self
    {
        return collect(self::cases())
            ->firstOrFail(fn (self $status): bool => $status->value() === $value);
    }
}
