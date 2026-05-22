<?php

namespace App\Enums;

enum ConformityResult
{
    case Conform;
    case Rejected;

    public function value(): string
    {
        return match ($this) {
            self::Conform => 'conforme',
            self::Rejected => 'rechazado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Conform => 'Conforme',
            self::Rejected => 'Rechazado',
        };
    }
}
