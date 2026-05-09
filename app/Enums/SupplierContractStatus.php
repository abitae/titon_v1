<?php

namespace App\Enums;

enum SupplierContractStatus
{
    case Draft;
    case InReview;
    case Approved;
    case Signed;
    case InExecution;
    case Finished;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::Draft => 'borrador',
            self::InReview => 'en_revision',
            self::Approved => 'aprobado',
            self::Signed => 'firmado',
            self::InExecution => 'en_ejecucion',
            self::Finished => 'finalizado',
            self::Cancelled => 'anulado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::InReview => 'En revision',
            self::Approved => 'Aprobado',
            self::Signed => 'Firmado',
            self::InExecution => 'En ejecucion',
            self::Finished => 'Finalizado',
            self::Cancelled => 'Anulado',
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
