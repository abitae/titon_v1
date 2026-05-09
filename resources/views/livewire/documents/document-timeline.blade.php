<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Timeline del documento</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $document->code }} · {{ $document->subject }}</p>
        </div>
        <a href="{{ route('documents.show', $document) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Volver al detalle</a>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="space-y-4">
            @forelse ($timeline as $entry)
                <div class="rounded-2xl border border-slate-200 p-5 dark:border-slate-800">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-medium text-slate-950 dark:text-white">{{ $entry['title'] }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $entry['actor'] ?? 'Sistema' }} · {{ optional($entry['created_at'])->format('d/m/Y H:i') }}</p>
                        </div>
                        <x-platform.status-badge :value="$entry['status']" />
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $entry['description'] }}</p>
                    @if (! empty(array_filter($entry['meta'])))
                        <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                            @if (! empty($entry['meta']['from_area']))
                                <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">Desde: {{ $entry['meta']['from_area'] }}</span>
                            @endif
                            @if (! empty($entry['meta']['to_area']))
                                <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">Hacia: {{ $entry['meta']['to_area'] }}</span>
                            @endif
                            @if (! empty($entry['meta']['to_user']))
                                <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">Responsable: {{ $entry['meta']['to_user'] }}</span>
                            @endif
                            @if (! empty($entry['meta']['attachment_count']))
                                <span class="rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">Adjuntos: {{ $entry['meta']['attachment_count'] }}</span>
                            @endif
                        </div>
                    @endif
                    @if (! empty($entry['attachments']))
                        <div class="mt-3 flex flex-col gap-2">
                            @foreach ($entry['attachments'] as $attachment)
                                <a href="{{ $attachment['url'] }}" target="_blank" class="text-sm font-medium text-cyan-700 hover:text-cyan-600 dark:text-cyan-300">
                                    {{ $attachment['name'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-slate-400">No hay eventos registrados para este documento.</p>
            @endforelse
        </div>
    </div>
</div>
