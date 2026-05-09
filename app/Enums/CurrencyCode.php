<?php

namespace App\Enums;

enum CurrencyCode
{
    case PEN;
    case USD;

    public function value(): string
    {
        return match ($this) {
            self::PEN => 'PEN',
            self::USD => 'USD',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PEN => 'Soles',
            self::USD => 'Dolares',
        };
    }

    public function symbol(): string
    {
        return match ($this) {
            self::PEN => 'S/',
            self::USD => '$',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $currency): string => $currency->value(),
            self::cases(),
        );
    }
}
