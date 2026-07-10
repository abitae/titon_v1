<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">{{ $this->title }}</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
            Prefijo activo:
            @if ($company->correlative_prefix)
                <span class="font-mono font-medium text-slate-950 dark:text-white">{{ strtoupper($company->correlative_prefix) }}</span>
            @else
                derivado automaticamente del nombre comercial hasta 12 caracteres.
            @endif
            Ajustelo en <a href="{{ route('companies.edit', $company) }}" class="text-cyan-600 hover:underline dark:text-cyan-400">Empresa</a>.
        </p>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
            Tokens: <span class="font-mono">{prefix}</span>,
            <span class="font-mono">{suffix}</span>,
            <span class="font-mono">{year}</span>,
            <span class="font-mono">{series}</span>,
            <span class="font-mono">{number}</span>.
        </p>
    </div>

    <x-platform.compact-table :headers="['Modulo', 'Sufijo', 'Plantilla', 'Longitud num.', 'Acciones']">
        @foreach ($formats as $format)
            <tr wire:key="correlative-format-{{ $format->id }}" class="align-top text-sm text-slate-700 dark:text-slate-200">
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $format->subjectEnum()->label() }}</p>
                    <p class="text-xs font-mono text-slate-500">{{ $format->subject }}</p>
                </td>
                <td class="px-2.5 py-1.5">
                    <input
                        wire:model.blur="draft.{{ $format->id }}.suffix"
                        class="block w-full min-w-[6rem] rounded-xl border border-slate-300 bg-white px-2 py-1.5 text-xs font-mono dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                    @error("draft.{$format->id}.suffix")
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </td>
                <td class="px-2.5 py-1.5">
                    <input
                        wire:model.blur="draft.{{ $format->id }}.template"
                        class="block w-full min-w-[16rem] rounded-xl border border-slate-300 bg-white px-2 py-1.5 text-xs font-mono dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                    @error("draft.{$format->id}.template")
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </td>
                <td class="px-2.5 py-1.5">
                    <input
                        type="number"
                        min="1"
                        max="12"
                        wire:model.blur="draft.{{ $format->id }}.pad_length"
                        class="block w-full max-w-[5rem] rounded-xl border border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    />
                    @error("draft.{$format->id}.pad_length")
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </td>
                <td class="px-2.5 py-1.5">
                    @can ('catalogs.editar')
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="saveRow({{ $format->id }})"
                                class="rounded-xl bg-slate-950 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400"
                            >
                                Guardar
                            </button>
                            <button
                                type="button"
                                wire:click="resetRow({{ $format->id }})"
                                wire:confirm="¿Restablecer la configuracion por defecto de este modulo?"
                                class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-900"
                            >
                                Restablecer
                            </button>
                        </div>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-platform.compact-table>
</div>
