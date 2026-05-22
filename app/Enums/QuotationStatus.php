<?php

namespace App\Enums;

enum QuotationStatus
{
    case Registered;
    case UnderEvaluation;
    case Selected;
    case NotSelected;
    case Observed;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::Registered => 'registrada',
            self::UnderEvaluation => 'en_evaluacion',
            self::Selected => 'seleccionada',
            self::NotSelected => 'no_seleccionada',
            self::Observed => 'observada',
            self::Cancelled => 'cancelada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registrada',
            self::UnderEvaluation => 'En evaluación',
            self::Selected => 'Seleccionada',
            self::NotSelected => 'No seleccionada',
            self::Observed => 'Observada',
            self::Cancelled => 'Cancelada',
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
