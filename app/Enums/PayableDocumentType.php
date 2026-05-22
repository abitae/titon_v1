<?php

namespace App\Enums;

enum PayableDocumentType
{
    case Invoice;
    case BankTransfer;
    case DeliveryNote;
    case ConformityAct;
    case TechnicalReport;
    case SignedOrder;
    case Other;

    public function value(): string
    {
        return match ($this) {
            self::Invoice => 'factura',
            self::BankTransfer => 'bancarizacion',
            self::DeliveryNote => 'guia_remision',
            self::ConformityAct => 'acta_conformidad',
            self::TechnicalReport => 'informe_tecnico',
            self::SignedOrder => 'orden_firmada',
            self::Other => 'otro',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Factura',
            self::BankTransfer => 'Bancarización',
            self::DeliveryNote => 'Guía de remisión',
            self::ConformityAct => 'Acta de conformidad',
            self::TechnicalReport => 'Informe técnico',
            self::SignedOrder => 'Orden firmada',
            self::Other => 'Otro',
        };
    }

    public function isRequiredByDefault(): bool
    {
        return match ($this) {
            self::Invoice, self::BankTransfer => true,
            default => false,
        };
    }
}
