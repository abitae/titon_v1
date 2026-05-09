<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MechanicsFlatExcelExport implements FromCollection, WithHeadings
{
    /**
     * @param  list<string>  $headings
     * @param  Collection<int, array<int|string, string|int|float|null>>  $rows
     */
    public function __construct(
        protected array $headings,
        protected Collection $rows,
    ) {}

    /**
     * @return Collection<int, array<int, string|int|float|null>>
     */
    public function collection(): Collection
    {
        return $this->rows->map(fn (array $row): array => array_values($row));
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
