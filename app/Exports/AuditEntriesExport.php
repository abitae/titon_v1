<?php

namespace App\Exports;

use App\Models\Audit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AuditEntriesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Audit>  $audits
     */
    public function __construct(
        protected Collection $audits,
    ) {}

    public function collection(): Collection
    {
        return $this->audits;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Fecha',
            'Empresa',
            'Usuario',
            'Rol activo',
            'Modulo',
            'Accion',
            'Modelo',
            'Registro ID',
            'IP',
            'Navegador',
            'Dispositivo',
            'Valores anteriores',
            'Valores nuevos',
            'Observacion',
        ];
    }

    /**
     * @return list<string>
     */
    public function map($row): array
    {
        return [
            $row->created_at?->format('d/m/Y H:i:s') ?? '',
            $row->company?->name ?? '',
            $row->user_name ?? '',
            $row->active_role ?? '',
            $row->module ?? '',
            $row->action ?? $row->event,
            class_basename((string) $row->auditable_type),
            (string) $row->auditable_id,
            $row->ip_address ?? '',
            $row->browser ?? '',
            $row->device ?? '',
            json_encode($row->old_values ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($row->new_values ?? [], JSON_UNESCAPED_UNICODE),
            $row->observation ?? '',
        ];
    }
}
