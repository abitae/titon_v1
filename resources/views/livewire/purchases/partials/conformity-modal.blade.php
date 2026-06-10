@php
    /** @var \App\Models\PurchaseOrder|null $conformityOrder */
@endphp

<x-platform.modal compact layer="top" :show="$showConformityModal" max-width="max-w-lg">
    @if ($conformityOrder)
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h2 class="text-sm font-semibold text-slate-950 dark:text-white">Conformidad en obra</h2>
                <p class="mt-0.5 truncate text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $conformityOrder->code }}
                    · {{ $conformityOrder->project?->name ?? 'Sin obra' }}
                    · {{ $conformityOrder->supplier?->business_name ?? 'Sin proveedor' }}
                </p>
                <p class="mt-1 text-[11px] text-violet-700 dark:text-violet-300">Al registrar conformidad, la orden generará una cuenta por pagar.</p>
            </div>
            <flux:tooltip content="Cerrar">
                <flux:button type="button" variant="ghost" size="sm" icon="x-mark" wire:click="closeConformityModal" class="!size-7 !min-h-0 !p-0" aria-label="Cerrar" />
            </flux:tooltip>
        </div>

        <form wire:submit="saveConformity" class="mt-2 space-y-2">
            <div class="grid gap-2 sm:grid-cols-2">
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Resultado</label>
                    <select wire:model.live="conformity_result" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('conformity_result') border-rose-500 @enderror">
                        @foreach ($conformityResultOptions as $conformityResultOption)
                            <option value="{{ $conformityResultOption->value() }}">{{ $conformityResultOption->label() }}</option>
                        @endforeach
                    </select>
                    @error('conformity_result') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fecha</label>
                    <input wire:model="conformity_date" type="date" class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('conformity_date') border-rose-500 @enderror" />
                    @error('conformity_date') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Observación
                    @if ($conformity_result === 'rechazado')
                        <span class="text-rose-600">*</span>
                    @endif
                </label>
                <textarea
                    wire:model="conformity_observation"
                    rows="2"
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('conformity_observation') border-rose-500 @enderror"
                    placeholder="{{ $conformity_result === 'rechazado' ? 'Indique el motivo del rechazo' : 'Observaciones opcionales' }}"
                ></textarea>
                @error('conformity_observation') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
            </div>

            @if ($conformity_result === 'conforme')
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Confirmación <span class="text-rose-600">*</span>
                    </label>
                    <input
                        wire:model="conformity_confirmation"
                        type="text"
                        autocomplete="off"
                        class="mt-1 block h-8 w-full rounded-lg border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-950 dark:text-white @error('conformity_confirmation') border-rose-500 @enderror"
                        placeholder="Escriba conforme para confirmar"
                    />
                    @error('conformity_confirmation') <p class="mt-0.5 text-[11px] text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endif

            @if ($conformityOrder->conformity)
                <p class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[11px] text-slate-600 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-300">
                    Registro previo: <x-platform.status-badge :value="$conformityOrder->conformity->result" size="xs" />
                </p>
            @endif

            <div class="flex items-center justify-end gap-2 pt-1">
                <flux:button type="button" variant="outline" wire:click="closeConformityModal" size="sm">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" size="sm">Guardar conformidad</flux:button>
            </div>
        </form>
    @endif
</x-platform.modal>
