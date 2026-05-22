<?php

namespace App\Enums;

enum RequirementStatus
{
    case Draft;
    case Created;
    case InProcess;
    case Attended;
    case Cancelled;

    public function value(): string
    {
        return match ($this) {
            self::Draft => 'borrador',
            self::Created => 'creado',
            self::InProcess => 'en_proceso',
            self::Attended => 'atendido',
            self::Cancelled => 'cancelado',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Created => 'Creado',
            self::InProcess => 'En proceso',
            self::Attended => 'Atendido',
            self::Cancelled => 'Cancelado',
        };
    }

    public function canEdit(): bool
    {
        return $this === self::Draft;
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
