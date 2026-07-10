<?php

namespace App\Support;

class DefaultDate
{
    public static function today(): string
    {
        return now()->toDateString();
    }

    public static function monthStart(): string
    {
        return now()->startOfMonth()->toDateString();
    }

    public static function daysAhead(int $days = 7): string
    {
        return now()->addDays($days)->toDateString();
    }

    public static function monthsAhead(int $months = 1): string
    {
        return now()->addMonths($months)->toDateString();
    }

    public static function yearsAhead(int $years = 1): string
    {
        return now()->addYears($years)->toDateString();
    }

    public static function nowDateTimeLocal(): string
    {
        return now()->format('Y-m-d\TH:i');
    }

    /**
     * @return array{from: string, to: string}
     */
    public static function filterRange(): array
    {
        return [
            'from' => self::monthStart(),
            'to' => self::today(),
        ];
    }
}
