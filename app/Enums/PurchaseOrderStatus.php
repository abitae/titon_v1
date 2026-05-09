<?php

namespace App\Enums;

enum PurchaseOrderStatus
{
    case Generated;
    case PendingApproval;
    case Approved;
    case Observed;
    case Cancelled;
    case ConvertedToContract;
    case Closed;

    public function value(): string
    {
        return match ($this) {
            self::Generated => 'generada',
            self::PendingApproval => 'en_aprobacion',
            self::Approved => 'aprobada',
            self::Observed => 'observada',
            self::Cancelled => 'anulada',
            self::ConvertedToContract => 'convertida_a_contrato',
            self::Closed => 'cerrada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Generada',
            self::PendingApproval => 'En aprobacion',
            self::Approved => 'Aprobada',
            self::Observed => 'Observada',
            self::Cancelled => 'Anulada',
            self::ConvertedToContract => 'Convertida a contrato',
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
