<?php

namespace App\Reports\Mechanics;

use App\Models\FleetEquipment;
use App\Models\User;
use App\Services\Pdf\MpdfBuilder;

class FleetEquipmentHistoryPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(User $actor, FleetEquipment $equipment): string
    {
        $equipment->load([
            'workProject',
            'responsibleUser',
            'equipmentType',
            'technicalInspections' => fn ($query) => $query
                ->with('responsibleUser')
                ->orderByDesc('reviewed_at')
                ->orderByDesc('due_at'),
            'preventiveMaintenances' => fn ($query) => $query
                ->with('responsibleUser')
                ->orderByDesc('scheduled_date'),
            'correctiveMaintenances' => fn ($query) => $query
                ->with('responsibleUser')
                ->orderByDesc('failure_at'),
            'workOrders' => fn ($query) => $query
                ->with(['responsibleUser', 'workProject'])
                ->orderByDesc('issued_at'),
        ]);

        return $this->mpdfBuilder->buildFromView('reports.mechanics.equipment-history-pdf', [
            'generatedAt' => now(),
            'actor' => $actor,
            'equipment' => $equipment,
        ], 'Historial '.$equipment->internal_code, $actor);
    }
}
