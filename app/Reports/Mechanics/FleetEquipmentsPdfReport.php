<?php

namespace App\Reports\Mechanics;

use App\Models\FleetEquipment;
use App\Models\User;
use App\Services\Pdf\MpdfBuilder;
use Illuminate\Contracts\View\Factory;

class FleetEquipmentsPdfReport
{
    public function __construct(
        protected MpdfBuilder $mpdfBuilder,
        protected Factory $viewFactory,
    ) {}

    public function build(User $actor): string
    {
        $equipments = FleetEquipment::query()
            ->with(['workProject', 'responsibleUser'])
            ->orderBy('internal_code')
            ->get();

        $html = $this->viewFactory->make('reports.mechanics.equipments-pdf', [
            'generatedAt' => now(),
            'actor' => $actor,
            'equipments' => $equipments,
        ])->render();

        return $this->mpdfBuilder->buildHtml($html, 'Equipos y maquinarias');
    }
}
