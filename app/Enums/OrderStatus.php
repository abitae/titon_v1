<?php

namespace App\Enums;

enum OrderStatus
{
    case Issued;
    case Sent;
    case InAttention;
    case Attended;
    case Conform;
    case Rejected;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::Issued => 'emitida',
            self::Sent => 'enviada',
            self::InAttention => 'en_atencion',
            self::Attended => 'atendida',
            self::Conform => 'conforme',
            self::Rejected => 'rechazada',
            self::Cancelled => 'anulada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Issued => 'Emitida',
            self::Sent => 'Enviada',
            self::InAttention => 'En atención',
            self::Attended => 'Atendida',
            self::Conform => 'Conforme',
            self::Rejected => 'Rechazada',
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
}
