<?php

namespace App\Support;

class Decimal
{
    /**
     * Compare two decimal values with the same semantics as bccomp().
     *
     * @return -1|0|1
     */
    public static function compare(string|float|int|null $left, string|float|int|null $right, int $scale = 3): int
    {
        if (function_exists('bccomp')) {
            return \bccomp((string) $left, (string) $right, $scale);
        }

        $leftFormatted = number_format((float) $left, $scale, '.', '');
        $rightFormatted = number_format((float) $right, $scale, '.', '');

        return strcmp($leftFormatted, $rightFormatted) <=> 0;
    }
}
