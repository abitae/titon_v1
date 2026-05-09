<?php

namespace App\Enums;

enum ContractPaymentScheduleStatus
{
    case Pending;
    case Partial;
    case Paid;
    case Expired;
    case Rescheduled;

    public function value(): string
    {
        return match ($this) {
            self::Pending => 'pendiente',
            self::Partial => 'parcial',
            self::Paid => 'pagado',
            self::Expired => 'vencido',
            self::Rescheduled => 'reprogramado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Partial => 'Parcial',
            self::Paid => 'Pagado',
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
