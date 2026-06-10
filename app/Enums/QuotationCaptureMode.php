<?php

namespace App\Enums;

enum QuotationCaptureMode
{
    case Form;
    case Pdf;

    public function value(): string
    {
        return match ($this) {
            self::Form => 'form',
            self::Pdf => 'pdf',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Form => 'Formulario',
            self::Pdf => 'Archivo PDF',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $mode): string => $mode->value(),
            self::cases(),
        );
    }

    public static function fromValue(string $value): self
    {
        return collect(self::cases())
            ->firstOrFail(fn (self $mode): bool => $mode->value() === $value);
    }
}
