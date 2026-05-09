<?php

namespace App\Enums;

enum FleetPreventiveMaintenanceStatus
{
    case Scheduled;
    case Pending;
    case InProgress;
    case Completed;
    case Expired;
    case Rescheduled;

    public function value(): string
    {
        return match ($this) {
            self::Scheduled => 'programado',
            self::Pending => 'pendiente',
            self::InProgress => 'en_proceso',
            self::Completed => 'realizado',
            self::Expired => 'vencido',
            self::Rescheduled => 'reprogramado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programado',
            self::Pending => 'Pendiente',
            self::InProgress => 'En proceso',
            self::Completed => 'Realizado',
            self::Expired => 'Vencido',
            self::Rescheduled => 'Reprogramado',
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
