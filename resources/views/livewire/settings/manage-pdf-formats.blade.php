<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $this->title }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Personaliza el membrete de todos los PDF exportados para
                <span class="font-medium text-slate-950 dark:text-white">{{ $company->name }}</span>.
            </p>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                El logotipo del PDF se configura en
                <a href="{{ route('companies.edit', $company) }}" class="text-cyan-600 hover:underline dark:text-cyan-400">Empresa</a>.
                Si la empresa no tiene logo, se usara el icono de la aplicacion desde
                <a href="{{ route('settings.catalogs') }}" class="text-cyan-600 hover:underline dark:text-cyan-400">Configuracion general</a>.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button variant="outline" size="sm" icon="arrow-path" wire:click="refreshPreviewFrame" type="button">
                Actualizar vista previa
            </flux:button>
            <flux:button variant="outline" size="sm" icon="eye" wire:click="previewPdf" type="button">
                Vista previa ampliada
            </flux:button>
            @can('pdf-formats.editar')
                <flux:button variant="primary" size="sm" icon="check" wire:click="save">Guardar formato</flux:button>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(300px,40%)]">
        <div class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Encabezado</h2>
                    <div class="mt-4 grid gap-3">
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Diseno</label>
                            <select wire:model="header_layout" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                                @foreach ($layoutOptions as $layout)
                                    <option value="{{ $layout->value }}">{{ $layout->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_logo" class="rounded" /> Mostrar logo</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_company_name" class="rounded" /> Nombre empresa</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_business_name" class="rounded" /> Razon social</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_ruc" class="rounded" /> RUC</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_address" class="rounded" /> Direccion</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_phone" class="rounded" /> Telefono</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_email" class="rounded" /> Correo</label>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Colores y pie de pagina</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Color primario</label>
                            <input type="color" wire:model="primary_color" class="mt-1 h-9 w-full rounded-xl border border-slate-300 bg-white px-1 dark:border-slate-700" />
                            @error('primary_color') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Color secundario</label>
                            <input type="color" wire:model="secondary_color" class="mt-1 h-9 w-full rounded-xl border border-slate-300 bg-white px-1 dark:border-slate-700" />
                            @error('secondary_color') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Texto de pie de pagina (opcional)</label>
                            <input wire:model="footer_text" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" placeholder="Ej: Documento generado por el sistema Titon" />
                            @error('footer_text') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="show_page_numbers" class="rounded" /> Numeracion de paginas</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="show_generated_at" class="rounded" /> Fecha de generacion</label>
                    </div>
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Margenes (mm)</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-4">
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Superior</label>
                        <input type="number" min="20" max="60" wire:model="margin_top" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        @error('margin_top') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Inferior</label>
                        <input type="number" min="10" max="40" wire:model="margin_bottom" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Izquierdo</label>
                        <input type="number" min="8" max="30" wire:model="margin_left" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Derecho</label>
                        <input type="number" min="8" max="30" wire:model="margin_right" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                </div>
            </section>

            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-300">
                <p class="font-medium text-slate-950 dark:text-white">Modulos que usan este formato</p>
                <p class="mt-1">Dashboard, compras (OC, cotizaciones, comparativas), contratos, mecanica (14 reportes), auditoria y cualquier exportacion futura.</p>
            </div>
        </div>

        <aside class="xl:sticky xl:top-4 xl:self-start">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Vista previa</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">Refleja los cambios del formulario antes de guardar.</p>
                </div>

                @if ($previewIframeUrl)
                    <div wire:key="pdf-format-preview-{{ md5($previewIframeUrl) }}" class="h-[min(72vh,720px)] bg-slate-100 dark:bg-slate-900">
                        <iframe
                            src="{{ $previewIframeUrl }}"
                            title="Vista previa del formato PDF"
                            class="h-full w-full"
                            loading="lazy"
                        ></iframe>
                    </div>
                @else
                    <div class="flex h-64 items-center justify-center px-4 text-center text-sm text-slate-500 dark:text-slate-400">
                        No se pudo cargar la vista previa.
                    </div>
                @endif
            </section>
        </aside>
    </div>

    <x-platform.pdf-viewer-modal
        :show="$showPdfModal"
        :url="$pdfViewerUrl"
        :title="$pdfViewerTitle"
        :subtitle="$pdfViewerSubtitle"
    />
</div>
