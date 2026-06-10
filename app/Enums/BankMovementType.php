<?php

namespace App\Enums;

enum BankMovementType
{
    case Deposit;
    case Withdrawal;
    case AccountsPayablePayment;
    case SupplierPayment;

    public function value(): string
    {
        return match ($this) {
            self::Deposit => 'deposit',
            self::Withdrawal => 'withdrawal',
            self::AccountsPayablePayment => 'accounts_payable_payment',
            self::SupplierPayment => 'supplier_payment',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Depósito',
            self::Withdrawal => 'Retiro',
            self::AccountsPayablePayment => 'Pago CxP',
            self::SupplierPayment => 'Pago a proveedor',
        };
    }

    public function direction(): BankMovementDirection
    {
        return match ($this) {
            self::Deposit => BankMovementDirection::Inbound,
            self::Withdrawal, self::AccountsPayablePayment, self::SupplierPayment => BankMovementDirection::Outbound,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $type): string => $type->value(),
            self::cases(),
        );
    }
}
