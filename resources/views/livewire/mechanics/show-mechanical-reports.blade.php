<div class="space-y-6">
    <x-mechanics.page-header
        :title="$title"
        description="Consulte los reportes en pantalla y expórtelos a PDF o Excel cuando lo necesite."
    />

    @forelse ($sections as $heading => $reports)
        <section class="space-y-4">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $heading }}</h2>

            @foreach ($reports as $report)
                <article
                    wire:key="mech-report-{{ $report['key'] }}"
                    class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ $report['label'] }}</h3>
                            @if (($report['summary'] ?? []) !== [])
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach ($report['summary'] as $line)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $line }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button
                                variant="outline"
                                size="sm"
                                icon="document-arrow-down"
                                href="{{ route($report['pdf_route']) }}"
                            >
                                Exportar PDF
                            </flux:button>
                            <flux:button
                                variant="outline"
                                size="sm"
                                icon="table-cells"
                                href="{{ route($report['excel_route']) }}"
                            >
                                Exportar Excel
                            </flux:button>
                        </div>
                    </div>

                    <div class="p-3">
                        @if ($report['rows']->isEmpty())
                            <p class="rounded-lg border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                No hay registros para mostrar en este reporte.
                            </p>
                        @else
                            <x-platform.compact-table dense :headers="$report['headings']">
                                @foreach ($report['rows'] as $row)
                                    <tr wire:key="mech-report-{{ $report['key'] }}-row-{{ $loop->index }}">
                                        @foreach ($row as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </x-platform.compact-table>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @empty
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/50">
            No tiene permisos para ver reportes de mecanica.
        </div>
    @endforelse
</div>
