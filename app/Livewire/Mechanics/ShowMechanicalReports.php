<?php

namespace App\Livewire\Mechanics;

use App\Services\Mechanics\MechanicsReportDatasets;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ShowMechanicalReports extends Component
{
    public string $title = 'Reportes de mecanica';

    /**
     * @return list<array{key: string, section: string, label: string, permission: string, pdf_route: string, excel_route: string}>
     */
    protected function reportDefinitions(): array
    {
        return [
            ['key' => 'equipments', 'section' => 'Equipos y maquinaria', 'label' => 'Equipos', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.equipments.pdf', 'excel_route' => 'mechanics.report.equipments.excel'],
            ['key' => 'machinery-status', 'section' => 'Equipos y maquinaria', 'label' => 'Estado de maquinaria', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.machinery-status.pdf', 'excel_route' => 'mechanics.report.machinery-status.excel'],
            ['key' => 'equipment-by-project', 'section' => 'Equipos y maquinaria', 'label' => 'Equipos por obra', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.equipment-by-project.pdf', 'excel_route' => 'mechanics.report.equipment-by-project.excel'],
            ['key' => 'inspections', 'section' => 'Revisiones y mantenimiento', 'label' => 'Revisiones tecnicas', 'permission' => 'revisiones.exportar', 'pdf_route' => 'mechanics.report.inspections.pdf', 'excel_route' => 'mechanics.report.inspections.excel'],
            ['key' => 'preventive', 'section' => 'Revisiones y mantenimiento', 'label' => 'Mantenimiento preventivo', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.preventive.pdf', 'excel_route' => 'mechanics.report.preventive.excel'],
            ['key' => 'corrective', 'section' => 'Revisiones y mantenimiento', 'label' => 'Mantenimiento correctivo', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.corrective.pdf', 'excel_route' => 'mechanics.report.corrective.excel'],
            ['key' => 'maintenance-costs', 'section' => 'Revisiones y mantenimiento', 'label' => 'Costos de mantenimiento', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.maintenance-costs.pdf', 'excel_route' => 'mechanics.report.maintenance-costs.excel'],
            ['key' => 'work-orders', 'section' => 'Ordenes de trabajo', 'label' => 'Detalle de OT', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.pdf', 'excel_route' => 'mechanics.report.work-orders.excel'],
            ['key' => 'work-orders-by-technician', 'section' => 'Ordenes de trabajo', 'label' => 'OT por tecnico', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.by-technician.pdf', 'excel_route' => 'mechanics.report.work-orders.by-technician.excel'],
            ['key' => 'work-orders-by-project', 'section' => 'Ordenes de trabajo', 'label' => 'OT por obra', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.by-project.pdf', 'excel_route' => 'mechanics.report.work-orders.by-project.excel'],
            ['key' => 'work-orders-by-equipment', 'section' => 'Ordenes de trabajo', 'label' => 'OT por equipo', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.by-equipment.pdf', 'excel_route' => 'mechanics.report.work-orders.by-equipment.excel'],
            ['key' => 'work-orders-overdue', 'section' => 'Ordenes de trabajo', 'label' => 'OT vencidas', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.overdue.pdf', 'excel_route' => 'mechanics.report.work-orders.overdue.excel'],
            ['key' => 'work-orders-types', 'section' => 'Ordenes de trabajo', 'label' => 'OT por tipo', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.types.pdf', 'excel_route' => 'mechanics.report.work-orders.types.excel'],
            ['key' => 'work-orders-costs', 'section' => 'Ordenes de trabajo', 'label' => 'Costos de OT', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.work-orders.costs.pdf', 'excel_route' => 'mechanics.report.work-orders.costs.excel'],
            ['key' => 'consumed-spares', 'section' => 'Repuestos', 'label' => 'Repuestos consumidos', 'permission' => 'mecanica.exportar', 'pdf_route' => 'mechanics.report.consumed-spares.pdf', 'excel_route' => 'mechanics.report.consumed-spares.excel'],
        ];
    }

    public function render(MechanicsReportDatasets $reportDatasets): View
    {
        /** @var Collection<string, Collection<int, array<string, mixed>>> $sections */
        $sections = collect($this->reportDefinitions())
            ->filter(fn (array $definition): bool => auth()->user()?->can($definition['permission']) ?? false)
            ->map(function (array $definition) use ($reportDatasets): array {
                $dataset = $reportDatasets->for($definition['key']);

                return [
                    ...$definition,
                    'headings' => $dataset['headings'],
                    'rows' => $dataset['rows'],
                    'summary' => $dataset['summary'],
                ];
            })
            ->groupBy('section');

        return view('livewire.mechanics.show-mechanical-reports', [
            'sections' => $sections,
        ])->layout('layouts.app', ['title' => $this->title]);
    }
}
