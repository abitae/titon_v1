<?php

namespace App\Exports;

use App\Models\FleetEquipment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements WithMapping<FleetEquipment>
 */
class FleetEquipmentsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, FleetEquipment>  $equipments
     */
    public function __construct(
        protected Collection $equipments,
    ) {}

    public function collection(): Collection
    {
        return $this->equipments;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Codigo',
            'Tipo',
            'Nombre',
            'Marca',
            'Modelo',
            'Serie',
            'Placa',
            'Ciudad',
            'Obra',
            'Responsable',
            'Estado',
            'Km',
            'Horometro',
        ];
    }

    /**
     * @param  FleetEquipment  $equipment
     * @return list<string|null>
     */
    public function map($equipment): array
    {
        return [
            $equipment->internal_code,
            $equipment->equipment_type,
            $equipment->name,
            $equipment->brand,
            $equipment->model,
            $equipment->serial_number,
            $equipment->plate,
            $equipment->city,
            $equipment->workProject?->code,
            $equipment->responsibleUser?->name,
            $equipment->operational_status,
            $equipment->odometer_km !== null ? (string) $equipment->odometer_km : '',
            $equipment->hour_meter !== null ? (string) $equipment->hour_meter : '',
        ];
    }
}
