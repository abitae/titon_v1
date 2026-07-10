<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Mensajes de contacto</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Bandeja de mensajes recibidos desde el formulario público.</p>
    </div>

    <x-platform.filter-bar class="xl:grid-cols-2">
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Buscar</label>
            <input wire:model.live.debounce.300ms="search" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Nombre, correo o asunto" />
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Estado</label>
            <select wire:model.live="readFilter" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <option value="all">Todos</option>
                <option value="unread">No leídos</option>
                <option value="read">Leídos</option>
            </select>
        </div>
    </x-platform.filter-bar>

    <x-platform.compact-table :headers="['Remitente', 'Asunto', 'Fecha', 'Estado', 'Acciones']">
        @forelse ($messages as $message)
            <tr class="text-xs text-slate-700 dark:text-slate-200" wire:key="contact-message-{{ $message->id }}">
                <td class="px-2.5 py-1.5">
                    <p class="font-medium text-slate-950 dark:text-white">{{ $message->name }}</p>
                    <p class="text-slate-500">{{ $message->email }}</p>
                </td>
                <td class="px-2.5 py-1.5">{{ $message->subject ?: Str::limit($message->message, 40) }}</td>
                <td class="px-2.5 py-1.5">{{ $message->created_at?->format('d/m/Y H:i') }}</td>
                <td class="px-2.5 py-1.5">
                    <x-platform.status-badge :value="$message->read_at ? 'active' : 'pendiente'" />
                </td>
                <td class="px-2.5 py-1.5">
                    <x-platform.action-buttons
                        :edit="'openDetailModal('.$message->id.')'"
                        :delete="'deleteMessage('.$message->id.')'"
                        delete-confirm="¿Eliminar este mensaje?"
                    />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No hay mensajes.</td>
            </tr>
        @endforelse
    </x-platform.compact-table>

    <div>{{ $messages->links() }}</div>

    <x-platform.modal :show="$showDetailModal && $selectedMessage !== null" max-width="max-w-2xl">
        @if ($selectedMessage)
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Mensaje de {{ $selectedMessage->name }}</h2>
                <button type="button" wire:click="closeModal" class="rounded-lg px-2 py-1 text-slate-500 hover:bg-slate-100">Cerrar</button>
            </div>

            <dl class="mt-6 space-y-4 text-sm">
                <div>
                    <dt class="font-medium text-slate-500">Correo</dt>
                    <dd class="text-slate-900 dark:text-white">{{ $selectedMessage->email }}</dd>
                </div>
                @if ($selectedMessage->phone)
                    <div>
                        <dt class="font-medium text-slate-500">Teléfono</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $selectedMessage->phone }}</dd>
                    </div>
                @endif
                @if ($selectedMessage->subject)
                    <div>
                        <dt class="font-medium text-slate-500">Asunto</dt>
                        <dd class="text-slate-900 dark:text-white">{{ $selectedMessage->subject }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="font-medium text-slate-500">Mensaje</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-slate-900 dark:text-white">{{ $selectedMessage->message }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" wire:click="deleteMessage({{ $selectedMessage->id }})" wire:confirm="¿Eliminar este mensaje?" class="rounded-xl border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700">Eliminar</button>
                @if (! $selectedMessage->read_at)
                    <button type="button" wire:click="markAsRead({{ $selectedMessage->id }})" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white dark:bg-cyan-500 dark:text-slate-950">Marcar leído</button>
                @endif
            </div>
        @endif
    </x-platform.modal>
</div>
