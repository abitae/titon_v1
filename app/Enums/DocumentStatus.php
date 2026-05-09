<?php

namespace App\Enums;

enum DocumentStatus
{
    case Registered;
    case InProgress;
    case Derived;
    case Received;
    case Observed;
    case InReview;
    case Attended;
    case Approved;
    case Rejected;
    case Archived;
    case Cancelled;
    case Closed;
    case Expired;

    public function value(): string
    {
        return match ($this) {
            self::Registered => 'registrado',
            self::InProgress => 'en_proceso',
            self::Derived => 'derivado',
            self::Received => 'recibido',
            self::Observed => 'observado',
            self::InReview => 'en_revision',
            self::Attended => 'atendido',
            self::Approved => 'aprobado',
            self::Rejected => 'rechazado',
            self::Archived => 'archivado',
            self::Cancelled => 'anulado',
            self::Closed => 'cerrado',
            self::Expired => 'vencido',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registrado',
            self::InProgress => 'En proceso',
            self::Derived => 'Derivado',
            self::Received => 'Recibido',
            self::Observed => 'Observado',
            self::InReview => 'En revision',
            self::Attended => 'Atendido',
            self::Approved => 'Aprobado',
            self::Rejected => 'Rechazado',
            self::Archived => 'Archivado',
            self::Cancelled => 'Anulado',
            self::Closed => 'Cerrado',
            self::Expired => 'Vencido',
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
