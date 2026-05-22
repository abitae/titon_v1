<?php

namespace App\Enums;

enum AccountsPayableStatus
{
    case PendingDocuments;
    case ReadyForPayment;
    case PartialPayment;
    case Paid;
    case Observed;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::PendingDocuments => 'pendiente_documentos',
            self::ReadyForPayment => 'lista_para_pago',
            self::PartialPayment => 'pago_parcial',
            self::Paid => 'pagada',
            self::Observed => 'observada',
            self::Cancelled => 'cancelada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PendingDocuments => 'Pendiente de documentos',
            self::ReadyForPayment => 'Lista para pago',
            self::PartialPayment => 'Pago parcial',
            self::Paid => 'Pagada',
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
