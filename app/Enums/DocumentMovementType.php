<?php

namespace App\Enums;

enum DocumentMovementType
{
    case Registered;
    case Reopened;
    case Derived;
    case Received;
    case Observed;
    case Attended;
    case Archived;
    case Cancelled;
    case Approved;
    case Rejected;
    case Closed;

    public function value(): string
    {
        return match ($this) {
            self::Registered => 'registered',
            self::Reopened => 'reopened',
            self::Derived => 'derived',
            self::Received => 'received',
            self::Observed => 'observed',
            self::Attended => 'attended',
            self::Archived => 'archived',
            self::Cancelled => 'cancelled',
            self::Approved => 'approved',
            self::Rejected => 'rejected',
            self::Closed => 'closed',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registro',
            self::Reopened => 'Reapertura',
            self::Derived => 'Derivacion',
            self::Received => 'Recepcion',
            self::Observed => 'Observacion',
            self::Attended => 'Atencion',
            self::Archived => 'Archivo',
            self::Cancelled => 'Anulacion',
            self::Approved => 'Aprobacion',
            self::Rejected => 'Rechazo',
            self::Closed => 'Cierre',
        };
    }
}
