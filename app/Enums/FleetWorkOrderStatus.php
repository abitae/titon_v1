<?php

namespace App\Enums;

enum FleetWorkOrderStatus
{
    case Generated;
    case Assigned;
    case InProgress;
    case Observed;
    case Finished;
    case Closed;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::Generated => 'generada',
            self::Assigned => 'asignada',
            self::InProgress => 'en_proceso',
            self::Observed => 'observada',
            self::Finished => 'finalizada',
            self::Closed => 'cerrada',
            self::Cancelled => 'anulada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Generada',
            self::Assigned => 'Asignada',
            self::InProgress => 'En proceso',
            self::Observed => 'Observada',
            self::Finished => 'Finalizada',
            self::Closed => 'Cerrada',
            self::Cancelled => 'Anulada',
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

    /**
     * @return array<int, string>
     */
    public static function openStatuses(): array
    {
        return [
            self::Generated->value(),
            self::Assigned->value(),
            self::InProgress->value(),
            self::Observed->value(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function countedCostStatuses(): array
    {
        return [
            self::Finished->value(),
            self::Closed->value(),
        ];
    }
}
