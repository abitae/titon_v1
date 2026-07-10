<div class="space-y-4">
    <x-mechanics.page-header :title="$title" description="Exportaciones PDF y Excel por empresa activa." />

    @php
        $sections = [
            'Equipos y maquinaria' => [
                ['Equipos', 'mechanics.report.equipments.pdf', 'mechanics.report.equipments.excel', 'mecanica.exportar'],
                ['Estado de maquinaria', 'mechanics.report.machinery-status.pdf', 'mechanics.report.machinery-status.excel', 'mecanica.exportar'],
                ['Equipos por obra', 'mechanics.report.equipment-by-project.pdf', 'mechanics.report.equipment-by-project.excel', 'mecanica.exportar'],
            ],
            'Revisiones y mantenimiento' => [
                ['Revisiones tecnicas', 'mechanics.report.inspections.pdf', 'mechanics.report.inspections.excel', 'revisiones.exportar'],
                ['Mantenimiento preventivo', 'mechanics.report.preventive.pdf', 'mechanics.report.preventive.excel', 'mecanica.exportar'],
                ['Mantenimiento correctivo', 'mechanics.report.corrective.pdf', 'mechanics.report.corrective.excel', 'mecanica.exportar'],
                ['Costos de mantenimiento', 'mechanics.report.maintenance-costs.pdf', 'mechanics.report.maintenance-costs.excel', 'mecanica.exportar'],
            ],
            'Ordenes de trabajo' => [
                ['Detalle de OT', 'mechanics.report.work-orders.pdf', 'mechanics.report.work-orders.excel', 'mecanica.exportar'],
                ['OT por tecnico', 'mechanics.report.work-orders.by-technician.pdf', 'mechanics.report.work-orders.by-technician.excel', 'mecanica.exportar'],
                ['OT por obra', 'mechanics.report.work-orders.by-project.pdf', 'mechanics.report.work-orders.by-project.excel', 'mecanica.exportar'],
                ['OT por equipo', 'mechanics.report.work-orders.by-equipment.pdf', 'mechanics.report.work-orders.by-equipment.excel', 'mecanica.exportar'],
                ['OT vencidas', 'mechanics.report.work-orders.overdue.pdf', 'mechanics.report.work-orders.overdue.excel', 'mecanica.exportar'],
                ['OT por tipo', 'mechanics.report.work-orders.types.pdf', 'mechanics.report.work-orders.types.excel', 'mecanica.exportar'],
                ['Costos de OT', 'mechanics.report.work-orders.costs.pdf', 'mechanics.report.work-orders.costs.excel', 'mecanica.exportar'],
            ],
            'Repuestos' => [
                ['Repuestos consumidos', 'mechanics.report.consumed-spares.pdf', 'mechanics.report.consumed-spares.excel', 'mecanica.exportar'],
            ],
        ];
    @endphp

    @foreach ($sections as $heading => $reports)
        @php
            $visibleReports = collect($reports)->filter(
                fn (array $report): bool => auth()->user()->can($report[3]),
            );
        @endphp

        @if ($visibleReports->isNotEmpty())
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">{{ $heading }}</h2>
                <x-platform.compact-table dense :headers="['Reporte', 'PDF', 'Excel']" class="mt-3">
                    @foreach ($visibleReports as [$label, $routePdf, $routeXlsx, $perm])
                        <tr wire:key="mech-report-{{ $routePdf }}">
                            <td class="font-medium text-slate-950 dark:text-white">{{ $label }}</td>
                            <td class="!px-1.5 !py-1">
                                <flux:button type="button" variant="outline" size="xs" icon="document-text" wire:click="openMechanicsReportPdf('{{ $routePdf }}', @js($label))">
                                    Ver PDF
                                </flux:button>
                            </td>
                            <td class="!px-1.5 !py-1">
                                <flux:button variant="outline" size="xs" icon="table-cells" href="{{ route($routeXlsx) }}">
                                    Descargar
                                </flux:button>
                            </td>
                        </tr>
                    @endforeach
                </x-platform.compact-table>
            </section>
        @endif
    @endforeach

    @if (! auth()->user()->can('mecanica.exportar') && ! auth()->user()->can('revisiones.exportar'))
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">
            No tiene permisos para exportar reportes de mecanica.
        </div>
    @endif

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
        :allowExternalOpen="false"
    />
</div>
