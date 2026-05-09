<?php

namespace App\Enums;

enum DocumentPriority
{
    case Low;
    case Medium;
    case High;
    case Critical;

    public function value(): string
    {
        return match ($this) {
            self::Low => 'baja',
            self::Medium => 'media',
            self::High => 'alta',
            self::Critical => 'critica',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Baja',
            self::Medium => 'Media',
            self::High => 'Alta',
            self::Critical => 'Critica',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $priority): string => $priority->value(),
            self::cases(),
        );
    }
}
