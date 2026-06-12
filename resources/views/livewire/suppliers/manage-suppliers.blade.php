<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Proveedores</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Activos</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['active']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Inactivos</p>
            <p class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">{{ number_format($summary['inactive']) }}</p>
        </div>
    </section>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Proveedores</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Consulta, registra y mantiene el padrón por empresa activa.</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
            Nuevo proveedor
        </button>
    </div>

    <x-platform.filter-bar class="xl:grid-cols-3">
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="RUC, razón social o nombre comercial" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Ciudad</label>
            <select wire:model.live="cityFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todas</option>
                @foreach ($cities as $cityOption)
                    <option value="{{ $cityOption->name }}">{{ $cityOption->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Estado</label>
            <select wire:model.live="statusFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="">Todos</option>
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['RUC', 'Proveedor', 'Ciudad', 'Estado', 'Banco', 'Acciones']">
        @forelse ($suppliers as $supplier)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="supplier-row-{{ $supplier->id }}">
                <td class="px-2.5 py-1.5 font-medium text-slate-950 dark:text-white">{{ $supplier->ruc }}</td>
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $supplier->business_name }}</p>
                    <p class="text-slate-500 dark:text-slate-400">{{ $supplier->commercial_name ?: 'Sin nombre comercial' }}</p>
                </td>
                <td class="px-2.5 py-1.5">{{ $supplier->city ?: 'Sin ciudad' }}</td>
                <td class="px-2.5 py-1.5"><x-platform.status-badge :value="$supplier->status" /></td>
                <td class="px-2.5 py-1.5">{{ $supplier->bank_name ?: 'Sin banco' }}</td>
                <td class="px-2.5 py-1.5">
                    <x-platform.action-buttons
                        :view="'openDetailModal('.$supplier->id.')'"
                        :edit="'openEditModal('.$supplier->id.')'"
                        :delete="'deleteSupplier('.$supplier->id.')'"
                        delete-confirm="¿Eliminar este proveedor?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No hay proveedores registrados para la empresa activa.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div class="rounded-3xl border border-slate-200 bg-white px-2.5 py-1.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        {{ $suppliers->links() }}
    </div>

    <x-platform.modal :show="$showFormModal">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $editingSupplierId ? 'Editar proveedor' : 'Nuevo proveedor' }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra datos comerciales, bancarios y archivos asociados.</p>
            </div>
            <button type="button" wire:click="closeModals" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">RUC</label>
                <input wire:model="ruc" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('ruc') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Razón social</label>
                <input wire:model="business_name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('business_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre comercial</label>
                <input wire:model="commercial_name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('commercial_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Contacto</label>
                <input wire:model="contact_name" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('contact_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Teléfono</label>
                <input wire:model="phone" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Correo</label>
                <input wire:model="email" type="email" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Dirección</label>
                <input wire:model="address" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('address') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Ciudad</label>
                <input wire:model="city" list="supplier-cities" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                <datalist id="supplier-cities">
                    @foreach ($cities as $cityOption)
                        <option value="{{ $cityOption->name }}"></option>
                    @endforeach
                </datalist>
                @error('city') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
                <select wire:model="status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption->value() }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Banco</label>
                <input wire:model="bank_name" list="supplier-banks" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                <datalist id="supplier-banks">
                    @foreach ($banks as $bank)
                        <option value="{{ $bank->name }}"></option>
                    @endforeach
                </datalist>
                @error('bank_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cuenta bancaria</label>
                <input wire:model="bank_account" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('bank_account') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">CCI</label>
                <input wire:model="cci" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('cci') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Archivos</label>
                <input wire:model="attachments" type="file" multiple class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                @error('attachments.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" wire:click="closeModals" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</button>
            <button type="button" wire:click="saveSupplier" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">Guardar</button>
        </div>
    </x-platform.modal>

    <x-platform.modal :show="$showDetailModal" max-width="max-w-3xl">
        @if ($selectedSupplier)
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950 dark:text-white">{{ $selectedSupplier->business_name }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">RUC {{ $selectedSupplier->ruc }}</p>
                </div>
                <button type="button" wire:click="closeModals" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">Cerrar</button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Estado</p>
                    <div class="mt-2"><x-platform.status-badge :value="$selectedSupplier->status" /></div>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Ciudad</p>
                    <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $selectedSupplier->city ?: 'Sin ciudad' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Contacto</p>
                    <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $selectedSupplier->contact_name ?: 'Sin contacto' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Banco</p>
                    <p class="mt-2 text-sm text-slate-900 dark:text-white">{{ $selectedSupplier->bank_name ?: 'Sin banco' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800 md:col-span-2">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Archivos asociados</p>
                    <div class="mt-3 flex flex-col gap-2">
                        @forelse ($selectedSupplier->attachments as $attachment)
                            <a href="{{ $attachment->url() }}" target="_blank" class="text-sm font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">
                                {{ $attachment->original_name }}
                            </a>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">No hay archivos asociados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </x-platform.modal>
</div>
