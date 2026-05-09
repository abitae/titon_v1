<?php

namespace App\Enums;

enum FleetTechnicalInspectionStatus
{
    case Valid;
    case DueSoon;
    case Expired;
    case Observed;

    public function value(): string
    {
        return match ($this) {
            self::Valid => 'vigente',
            self::DueSoon => 'por_vencer',
            self::Expired => 'vencido',
            self::Observed => 'observado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Vigente',
            self::DueSoon => 'Por vencer',
            self::Expired => 'Vencido',
            self::Observed => 'Observado',
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
