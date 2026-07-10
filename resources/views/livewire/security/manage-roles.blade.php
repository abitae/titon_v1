<div class="flex flex-1 flex-col gap-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Seguridad</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950 dark:text-white">{{ $title }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Perfiles de acceso y permisos asignados por rol.
                </p>
            </div>

            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar rol"
                class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            >
        </div>
    </section>

    <x-platform.compact-table :headers="['Rol', 'Permisos', 'Acciones']">
        @forelse ($roles as $role)
            <tr wire:key="role-{{ $role->id }}" class="align-top">
                <td>
                    <p class="font-medium text-slate-950 dark:text-white">{{ $role->name }}</p>
                </td>
                <td class="text-slate-600 dark:text-slate-300">
                    {{ $role->permissions_count }} permisos
                </td>
                <td>
                    @if ($canEdit && $role->name !== 'Super Admin')
                        <flux:button wire:click="editRole({{ $role->id }})" variant="ghost" size="sm">
                            Editar permisos
                        </flux:button>
                    @else
                        <span class="text-xs text-slate-400">Solo lectura</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="py-8 text-center text-sm text-slate-500">No hay roles registrados.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    @if ($showEditModal)
        <x-platform.modal :show="$showEditModal" max-width="max-w-3xl">
            <div class="space-y-4">
                <flux:heading size="lg">Editar permisos del rol</flux:heading>

                <div class="max-h-[60vh] space-y-4 overflow-y-auto pe-2">
                    @foreach ($permissionGroups as $module => $permissions)
                        <div wire:key="module-{{ $module }}" class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                            <p class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">
                                {{ str($module)->replace(['-', '_'], ' ')->title() }}
                            </p>

                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($permissions as $permission)
                                    <label wire:key="permission-{{ $permission->id }}" class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300">
                                        <input
                                            type="checkbox"
                                            wire:model="selectedPermissions"
                                            value="{{ $permission->name }}"
                                            class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                        >
                                        <span>
                                            <span class="font-medium">{{ $permission->name }}</span>
                                            <span class="mt-0.5 block text-xs text-slate-500">{{ $permission->description }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button wire:click="cancelEdit" variant="ghost">Cancelar</flux:button>
                    <flux:button wire:click="saveRolePermissions" variant="primary">Guardar</flux:button>
                </div>
            </div>
        </x-platform.modal>
    @endif
</div>
