<?php

namespace App\Exports;

abstract class BaseExport
{
    /**
     * @return array<int, string>
     */
    abstract public function headings(): array;

    /**
     * @return iterable<int, array<string, mixed>>
     */
    abstract public function rows(): iterable;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return [];
    }
}
