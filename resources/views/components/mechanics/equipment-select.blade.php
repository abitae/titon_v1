@props([
    'options' => [],
    'selectedValue' => null,
    'searchModel' => 'equipment_search',
    'selectMethod' => 'selectFleetEquipment',
    'clearMethod' => null,
    'label' => 'Equipo',
    'placeholder' => 'Buscar por código o nombre...',
    'error' => null,
    'allowClear' => false,
])

<div>
    <x-platform.searchable-select
        :label="$label"
        :options="$options"
        option-value="id"
        option-label="label"
        option-secondary="name"
        :search-model="$searchModel"
        :selected-value="$selectedValue"
        :select-method="$selectMethod"
        :placeholder="$placeholder"
        empty-text="Sin equipos coincidentes"
        :error="$error"
    />

@if ($allowClear && filled($selectedValue) && $clearMethod)
        <div class="mt-0.5 flex justify-end">
            <button
                type="button"
                wire:click="{{ $clearMethod }}"
                class="text-[10px] font-medium text-cyan-700 hover:underline dark:text-cyan-400"
            >
                Quitar selección
            </button>
        </div>
    @endif
</div>
