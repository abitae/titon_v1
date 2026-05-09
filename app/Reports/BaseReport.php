<?php

namespace App\Reports;

abstract class BaseReport
{
    abstract public function title(): string;

    /**
     * @return array<string, mixed>
     */
    abstract public function filters(): array;

    /**
     * @return array<string, mixed>
     */
    abstract public function data(): array;

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [];
    }
}
