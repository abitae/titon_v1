<?php

namespace App\Enums;

enum FleetCorrectiveMaintenanceStatus
{
    case Reported;
    case InDiagnosis;
    case InRepair;
    case Repaired;
    case Closed;

    public function value(): string
    {
        return match ($this) {
            self::Reported => 'reportado',
            self::InDiagnosis => 'en_diagnostico',
            self::InRepair => 'en_reparacion',
            self::Repaired => 'reparado',
            self::Closed => 'cerrado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Reported => 'Reportado',
            self::InDiagnosis => 'En diagnóstico',
            self::InRepair => 'En reparación',
            self::Repaired => 'Reparado',
            self::Closed => 'Cerrado',
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
