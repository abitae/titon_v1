@props(['show' => false, 'url' => '', 'title' => 'Documento PDF', 'subtitle' => 'Vista previa del documento PDF', 'allowExternalOpen' => true])

@if ($show)
    <div class="platform-modal-backdrop fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-slate-900/40 px-3 py-4 backdrop-blur-sm dark:bg-slate-950/60">
        <div class="relative w-full max-w-5xl rounded-xl border border-slate-200 bg-white p-4 shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-base font-semibold text-slate-950 dark:text-white">{{ $title }}</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    @if ($url && $allowExternalOpen)
                        <flux:tooltip content="Abrir en pestaña nueva">
                            <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square" href="{{ $url }}" target="_blank" class="!size-7 !min-h-0 !p-0" aria-label="Abrir en pestaña nueva" />
                        </flux:tooltip>
                    @endif
                    <flux:tooltip content="Cerrar">
                        <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closePdfModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
                    </flux:tooltip>
                </div>
            </div>

            @if ($url)
                <div wire:ignore.self wire:key="pdf-preview-{{ md5($url) }}" class="mt-3 h-[70vh] w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-950">
                    <iframe
                        src="{{ $url }}"
                        title="{{ $title }}"
                        class="h-full w-full"
                        loading="lazy"
                    ></iframe>
                </div>
            @else
                <div class="mt-3 flex h-[70vh] items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-sm text-slate-500 dark:text-slate-400">No se pudo cargar el documento PDF.</p>
                </div>
            @endif
        </div>
    </div>
@endif
