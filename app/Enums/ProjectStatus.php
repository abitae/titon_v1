<?php

namespace App\Enums;

enum ProjectStatus
{
    case Planned;
    case InProgress;
    case Paused;
    case Completed;
    case Closed;

    public function value(): string
    {
        return match ($this) {
            self::Planned => 'planificada',
            self::InProgress => 'en_ejecucion',
            self::Paused => 'paralizada',
            self::Completed => 'finalizada',
            self::Closed => 'cerrada',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planificada',
            self::InProgress => 'En ejecucion',
            self::Paused => 'Paralizada',
            self::Completed => 'Finalizada',
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

    public static function fromValue(string $value): self
    {
        return collect(self::cases())
            ->firstOrFail(fn (self $status): bool => $status->value() === $value);
    }
}
