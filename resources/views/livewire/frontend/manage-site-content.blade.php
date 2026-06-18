<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Contenido del sitio web</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Administra textos, imágenes y enlaces del sitio público.</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50/90 p-2 dark:border-slate-800 dark:bg-slate-950/60">
            @foreach ($groups as $groupKey => $groupLabel)
                <button
                    type="button"
                    wire:click="selectGroup('{{ $groupKey }}')"
                    class="rounded-xl px-4 py-2.5 text-sm font-medium transition {{ $selectedGroup === $groupKey ? 'bg-white text-cyan-800 shadow-sm ring-1 ring-slate-200/90 dark:bg-slate-800 dark:text-cyan-300 dark:ring-slate-700' : 'text-slate-600 hover:bg-white/80 dark:text-slate-400 dark:hover:bg-slate-800/60' }}"
                >
                    {{ $groupLabel }}
                </button>
            @endforeach
        </div>

        <div class="p-4 sm:p-6">
            @if ($selectedGroup === 'brand')
                <div class="mx-auto max-w-3xl space-y-8">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Identidad de marca</h2>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Configura el logotipo, nombre y favicon del sitio público.</p>
                    </div>

                    <div class="grid gap-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                            <div class="flex items-start gap-4">
                                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                    @if ($brandLogo)
                                        <img src="{{ $brandLogo->temporaryUrl() }}" alt="Vista previa del logotipo" class="size-full object-cover" />
                                    @elseif ($currentBrandLogoUrl)
                                        <img src="{{ $currentBrandLogoUrl }}" alt="{{ $brandName ?: 'Logotipo' }}" class="size-full object-cover" />
                                    @else
                                        <x-app-logo-icon class="size-10 fill-current text-slate-700 dark:text-slate-100" />
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Logotipo</label>
                                        <input type="file" wire:model="brandLogo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="mt-2 block w-full text-sm" />
                                        @error('brandLogo') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    @if ($currentBrandLogoUrl || $brandLogo)
                                        <button type="button" wire:click="removeBrandLogo" class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                                            Quitar logotipo
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre de la empresa (opcional)</label>
                            <input wire:model="brandName" type="text" maxlength="120" placeholder="Ej. Titon Infraestructura" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            <p class="mt-1 text-xs text-slate-500">Si se deja vacío, se usará el nombre configurado en la aplicación.</p>
                            @error('brandName') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                            <div class="flex items-start gap-4">
                                <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                    @if ($brandFavicon)
                                        <img src="{{ $brandFavicon->temporaryUrl() }}" alt="Vista previa del favicon" class="size-full object-cover" />
                                    @elseif ($currentBrandFaviconUrl)
                                        <img src="{{ $currentBrandFaviconUrl }}" alt="Favicon actual" class="size-full object-cover" />
                                    @else
                                        <span class="text-xs font-medium text-slate-400">ICO</span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Favicon</label>
                                        <input type="file" wire:model="brandFavicon" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/svg+xml,image/jpeg,image/webp,.ico" class="mt-2 block w-full text-sm" />
                                        <p class="mt-1 text-xs text-slate-500">PNG, ICO, SVG, JPG o WebP. Máximo 1 MB.</p>
                                        @error('brandFavicon') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    @if ($currentBrandFaviconUrl || $brandFavicon)
                                        <button type="button" wire:click="removeBrandFavicon" class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                                            Quitar favicon
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                        <button type="button" wire:click="saveBrand" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">
                            Guardar marca
                        </button>
                    </div>
                </div>
            @else
            <x-platform.compact-table :headers="['Clave', 'Título', 'Estado', 'Orden', 'Acciones']">
                @forelse ($settings as $setting)
                    <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="site-setting-{{ $setting->id }}">
                        <td class="px-2.5 py-1.5 font-mono text-slate-500">{{ $setting->key }}</td>
                        <td class="px-2.5 py-1.5">
                            <p class="font-medium text-slate-950 dark:text-white">{{ $setting->title ?: '—' }}</p>
                            <p class="text-slate-500">{{ Str::limit($setting->subtitle ?: $setting->body, 60) }}</p>
                        </td>
                        <td class="px-2.5 py-1.5">
                            <x-platform.status-badge :value="$setting->is_active ? 'active' : 'inactive'" />
                        </td>
                        <td class="px-2.5 py-1.5">{{ $setting->sort_order }}</td>
                        <td class="px-2.5 py-1.5">
                            <x-platform.action-buttons :edit="'openEditModal('.$setting->id.')'" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No hay secciones para este grupo.</td>
                    </tr>
                @endforelse
            </x-platform.compact-table>
            @endif
        </div>
    </div>

    <x-platform.modal :show="$editingSettingId !== null" max-width="max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Editar sección</h2>
                <p class="mt-1 font-mono text-sm text-slate-500">{{ $key }}</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Título</label>
                <input wire:model="sectionTitle" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('sectionTitle') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Subtítulo</label>
                <input wire:model="subtitle" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Contenido</label>
                <textarea wire:model="body" rows="4" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Texto del botón</label>
                    <input wire:model="cta_label" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">URL del botón</label>
                    <input wire:model="cta_url" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Imagen</label>
                @if ($currentImageUrl)
                    <img src="{{ $currentImageUrl }}" alt="" class="mt-2 h-24 rounded-lg object-cover" />
                @endif
                <input type="file" wire:model="image" accept="image/*" class="mt-2 block w-full text-sm" />
                @error('image') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Orden</label>
                    <input wire:model="sort_order" type="number" min="0" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div class="flex items-center gap-3 pt-8">
                    <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-cyan-600" />
                    <label class="text-sm text-slate-700 dark:text-slate-200">Activo</label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveSetting" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950">Guardar</button>
        </div>
    </x-platform.modal>
</div>
