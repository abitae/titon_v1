<?php

namespace App\Enums;

enum FleetWorkOrderType
{
    case Preventive;
    case Corrective;
    case Inspection;
    case TechnicalInspection;

    public function value(): string
    {
        return match ($this) {
            self::Preventive => 'preventivo',
            self::Corrective => 'correctivo',
            self::Inspection => 'inspeccion',
            self::TechnicalInspection => 'revision_tecnica',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Preventive => 'Preventivo',
            self::Corrective => 'Correctivo',
            self::Inspection => 'Inspección',
            self::TechnicalInspection => 'Revisión técnica',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $type): string => $type->value(),
            self::cases(),
        );
    }
}
