<?php

namespace App\Enums;

enum PdfHeaderLayout: string
{
    case Classic = 'classic';
    case Banner = 'banner';

    public function label(): string
    {
        return match ($this) {
            self::Classic => 'Clasico (logo lateral)',
            self::Banner => 'Banner (franja superior)',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $layout): string => $layout->value, self::cases());
    }
}
