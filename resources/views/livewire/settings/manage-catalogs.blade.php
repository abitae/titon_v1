<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Configuración general</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Administra ciudades, bancos, métodos de pago y catálogos operativos por empresa.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
            Nuevo ítem
        </button>
    </div>

    <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach ($types as $type)
            <button
                type="button"
                wire:click="$set('selectedType', '{{ $type->value() }}')"
                class="rounded-2xl border px-4 py-3 text-left text-sm transition {{ $selectedType === $type->value() ? 'border-cyan-400 bg-cyan-50 text-cyan-800 dark:border-cyan-500 dark:bg-cyan-950/40 dark:text-cyan-200' : 'border-slate-200 bg-white text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200' }}"
            >
                {{ $type->label() }}
            </button>
        @endforeach
    </div>

    <x-platform.filter-bar class="xl:grid-cols-2">
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nombre o código" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="activeFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Todos</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Nombre', 'Código', 'Estado', 'Orden', 'Acciones']">
        @forelse ($items as $item)
            <tr class="text-sm text-slate-700 dark:text-slate-200" wire:key="catalog-row-{{ $item->id }}">
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $item->name }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $item->description ?: 'Sin descripción' }}</p>
                </td>
                <td class="px-6 py-4">{{ $item->code ?: 'Sin código' }}</td>
                <td class="px-6 py-4">
                    <x-platform.status-badge :value="$item->is_active ? 'active' : 'inactive'" />
                </td>
                <td class="px-6 py-4">{{ $item->sort_order }}</td>
                <td class="px-6 py-4">
                    <x-platform.action-buttons
                        :edit="'openEditModal('.$item->id.')'"
                        :delete="'deleteCatalogItem('.$item->id.')'"
                        delete-confirm="¿Eliminar este catálogo?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay ítems para este catálogo en la empresa activa.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $items->links() }}
    </div>

    <x-platform.modal :show="$showFormModal" max-width="max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingCatalogId ? 'Editar ítem' : 'Nuevo ítem' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Catálogo actual: {{ \App\Enums\CatalogType::fromValue($selectedType)->label() }}</p>
            </div>
            <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre</label>
                <input wire:model="name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Código</label>
                <input wire:model="code" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('code') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción</label>
                <textarea wire:model="description" rows="3" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"></textarea>
                @error('description') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Orden</label>
                <input wire:model="sort_order" type="number" min="0" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('sort_order') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-3 pt-8">
                <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                <label class="text-sm text-slate-700 dark:text-slate-200">Activo</label>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModal" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveCatalogItem" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">Guardar</button>
        </div>
    </x-platform.modal>
</div>
