<?php

namespace App\Enums;

enum BankMovementDirection
{
    case Inbound;
    case Outbound;

    public function value(): string
    {
        return match ($this) {
            self::Inbound => 'inbound',
            self::Outbound => 'outbound',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Inbound => 'Entrada',
            self::Outbound => 'Salida',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $direction): string => $direction->value(),
            self::cases(),
        );
    }
}
