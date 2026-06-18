<?php

namespace App\Enums;

enum WarehouseMovementSource
{
    case OrderConformity;
    case ManualOutbound;
    case TransferOutbound;
    case TransferInbound;
    case Adjustment;

    public function value(): string
    {
        return match ($this) {
            self::OrderConformity => 'conformidad_orden',
            self::ManualOutbound => 'salida_manual',
            self::TransferOutbound => 'transferencia_salida',
            self::TransferInbound => 'transferencia_entrada',
            self::Adjustment => 'ajuste',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::OrderConformity => 'Conformidad de orden',
            self::ManualOutbound => 'Salida manual',
            self::TransferOutbound => 'Transferencia (salida)',
            self::TransferInbound => 'Transferencia (entrada)',
            self::Adjustment => 'Ajuste',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $source): string => $source->value(),
            self::cases(),
        );
    }
}
