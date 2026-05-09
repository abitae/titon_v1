<?php

namespace App\Enums;

enum PurchaseRequestStatus
{
    case Draft;
    case Requested;
    case Quoting;
    case Quoted;
    case UnderReview;
    case Awarded;
    case Ordered;
    case Closed;

    public function value(): string
    {
        return match ($this) {
            self::Draft => 'borrador',
            self::Requested => 'solicitada',
            self::Quoting => 'en_cotizacion',
            self::Quoted => 'cotizada',
            self::UnderReview => 'en_evaluacion',
            self::Awarded => 'adjudicada',
            self::Ordered => 'orden_generada',
            self::Closed => 'cerrada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Requested => 'Solicitada',
            self::Quoting => 'En cotizacion',
            self::Quoted => 'Cotizada',
            self::UnderReview => 'En evaluacion',
            self::Awarded => 'Adjudicada',
            self::Ordered => 'Orden generada',
            self::Closed => 'Cerrada',
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
