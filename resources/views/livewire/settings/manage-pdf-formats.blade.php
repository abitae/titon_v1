<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $this->title }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                Personaliza el membrete de los PDF exportados para
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
                <flux:button variant="outline" size="sm" icon="arrow-path" wire:click="resetToDefaults" type="button">Restablecer</flux:button>
                <flux:button variant="primary" size="sm" icon="check" wire:click="save">Guardar formato</flux:button>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(300px,40%)]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(240px,360px)] md:items-end">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Empresa a personalizar</h2>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Cada empresa conserva su propio membrete, logo, margenes, colores y pie de pagina.</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Empresa</label>
                        <select wire:model="selected_company_id" wire:change="selectCompany" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                            @foreach ($availableCompanies as $availableCompany)
                                <option value="{{ $availableCompany->id }}">{{ $availableCompany->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Disenos preestablecidos</h2>
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">Aplica una base profesional y ajusta cada campo antes de guardar.</p>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach ($presetOptions as $presetKey => $preset)
                        <button
                            type="button"
                            wire:click="applyPreset('{{ $presetKey }}')"
                            class="group rounded-xl border border-slate-200 bg-slate-50 p-3 text-left transition hover:border-cyan-300 hover:bg-cyan-50 dark:border-slate-800 dark:bg-slate-900/50 dark:hover:border-cyan-700 dark:hover:bg-cyan-950/40"
                        >
                            <span class="text-sm font-semibold text-slate-950 group-hover:text-cyan-700 dark:text-white dark:group-hover:text-cyan-300">{{ $preset['label'] }}</span>
                            <span class="mt-1 block text-[11px] leading-4 text-slate-500 dark:text-slate-400">{{ $preset['description'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Encabezado</h2>
                    <div class="mt-4 grid gap-3">
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Diseno</label>
                            <select wire:model="header_layout" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                                @foreach ($layoutOptions as $layout)
                                    <option value="{{ $layout->value }}">{{ $layout->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Alineacion texto</label>
                            <select wire:model="header_text_align" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                                <option value="left">Izquierda</option>
                                <option value="center">Centro</option>
                                <option value="right">Derecha</option>
                            </select>
                            @error('header_text_align') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_logo" wire:change="refreshPreviewFrame" class="rounded" /> Mostrar logo</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_company_name" wire:change="refreshPreviewFrame" class="rounded" /> Nombre empresa</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_business_name" wire:change="refreshPreviewFrame" class="rounded" /> Razon social</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_ruc" wire:change="refreshPreviewFrame" class="rounded" /> RUC</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_address" wire:change="refreshPreviewFrame" class="rounded" /> Direccion</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_phone" wire:change="refreshPreviewFrame" class="rounded" /> Telefono</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_email" wire:change="refreshPreviewFrame" class="rounded" /> Correo</label>
                        </div>
                        <div class="grid gap-3 border-t border-slate-200 pt-3 dark:border-slate-800 sm:grid-cols-2">
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Ancho logo (mm)</label>
                                <input type="number" min="16" max="80" wire:model="logo_width" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                                @error('logo_width') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Alto logo (mm)</label>
                                <input type="number" min="8" max="40" wire:model="logo_height" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                                @error('logo_height') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Ubicacion</label>
                                <select wire:model="logo_position" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                                    <option value="left">Izquierda</option>
                                    <option value="right">Derecha</option>
                                </select>
                                @error('logo_position') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Alineacion vertical</label>
                                <select wire:model="logo_vertical_align" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">
                                    <option value="top">Superior</option>
                                    <option value="middle">Centro</option>
                                    <option value="bottom">Inferior</option>
                                </select>
                                @error('logo_vertical_align') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Colores y pie de pagina</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Color primario</label>
                            <input type="color" wire:model="primary_color" wire:change="refreshPreviewFrame" class="mt-1 h-9 w-full rounded-xl border border-slate-300 bg-white px-1 dark:border-slate-700" />
                            @error('primary_color') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Color secundario</label>
                            <input type="color" wire:model="secondary_color" wire:change="refreshPreviewFrame" class="mt-1 h-9 w-full rounded-xl border border-slate-300 bg-white px-1 dark:border-slate-700" />
                            @error('secondary_color') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Texto de pie de pagina (opcional)</label>
                            <input wire:model="footer_text" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" placeholder="Ej: Documento generado por el sistema Titon" />
                            @error('footer_text') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tamano pie</label>
                            <input type="number" min="7" max="12" wire:model="footer_font_size" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                            @error('footer_font_size') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="show_footer_border" wire:change="refreshPreviewFrame" class="rounded" /> Linea superior pie</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="show_page_numbers" wire:change="refreshPreviewFrame" class="rounded" /> Numeracion de paginas</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="show_generated_at" wire:change="refreshPreviewFrame" class="rounded" /> Fecha de generacion</label>
                    </div>
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Detalle visual</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Espaciado encabezado</label>
                        <input type="number" min="4" max="18" wire:model="header_padding" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        @error('header_padding') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tamano titulo</label>
                        <input type="number" min="10" max="20" wire:model="title_font_size" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        @error('title_font_size') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tamano datos</label>
                        <input type="number" min="7" max="13" wire:model="meta_font_size" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        @error('meta_font_size') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="show_header_rule" wire:change="refreshPreviewFrame" class="rounded" /> Linea divisoria</label>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Grosor linea</label>
                        <input type="number" min="1" max="5" wire:model="header_rule_thickness" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        @error('header_rule_thickness') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-950">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Margenes (mm)</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-4">
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Superior</label>
                        <input type="number" min="20" max="60" wire:model="margin_top" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        @error('margin_top') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Inferior</label>
                        <input type="number" min="10" max="40" wire:model="margin_bottom" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Izquierdo</label>
                        <input type="number" min="8" max="30" wire:model="margin_left" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Derecho</label>
                        <input type="number" min="8" max="30" wire:model="margin_right" wire:change="refreshPreviewFrame" class="mt-1 block h-9 w-full rounded-xl border border-slate-300 bg-white px-2.5 text-sm dark:border-slate-700 dark:bg-slate-950" />
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
                    <div wire:key="pdf-format-preview-{{ md5($previewIframeUrl) }}" class="relative h-[min(72vh,720px)] bg-slate-100 dark:bg-slate-900">
                        <div wire:loading.flex wire:target="refreshPreviewFrame,applyPreset,resetToDefaults" class="absolute inset-x-0 top-0 z-10 justify-center bg-white/90 px-3 py-2 text-xs font-medium text-slate-600 shadow-sm dark:bg-slate-950/90 dark:text-slate-300">
                            Actualizando vista previa...
                        </div>
                        <iframe
                            src="{{ $previewIframeUrl }}#toolbar=0&navpanes=0&view=FitH"
                            title="Vista previa del formato PDF"
                            class="h-full w-full"
                            loading="eager"
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
