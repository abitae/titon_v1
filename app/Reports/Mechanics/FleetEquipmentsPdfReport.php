<?php

namespace App\Reports\Mechanics;

use App\Models\FleetEquipment;
use App\Models\User;
use App\Services\Pdf\MpdfBuilder;

class FleetEquipmentsPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
    ) {}

    public function build(User $actor): string
    {
        $equipments = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser'])
            ->orderBy('internal_code')
            ->get();

        return $this->mpdfBuilder->buildFromView('reports.mechanics.equipments-pdf', [
            'generatedAt' => now(),
            'actor' => $actor,
            'equipments' => $equipments,
        ], 'Equipos y maquinarias', $actor);
    }
}
