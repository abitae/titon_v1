<?php

namespace App\Enums;

enum InvitationStatus
{
    case Pending;
    case Sent;
    case Responded;
    case Expired;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::Pending => 'pendiente',
            self::Sent => 'enviado',
            self::Responded => 'respondido',
            self::Expired => 'vencido',
            self::Cancelled => 'cancelado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Sent => 'Enviado',
            self::Responded => 'Respondido',
            self::Vencido => 'Vencido',
            self::Cancelled => 'Cancelado',
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
