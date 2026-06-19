@props([
    'fullWidth' => false,
])

@php
    $webmailUrl = config('frontend.webmail_url');
    $outlookManualUrl = asset(config('frontend.outlook_manual_path'));
@endphp

<flux:modal.trigger name="frontend-mail-access">
    <flux:button
        variant="ghost"
        :class="$fullWidth ? 'w-full justify-center' : ''"
        aria-label="Correo"
        data-test="frontend-mail-access-trigger"
    >
        <flux:icon name="envelope" class="size-5" />
        @if ($fullWidth)
            <span>Correo</span>
        @endif
    </flux:button>
</flux:modal.trigger>

@once
    <flux:modal name="frontend-mail-access" class="max-w-3xl">
        <div x-data="{ showOutlook: false }">
            <div x-show="! showOutlook" x-cloak class="space-y-6">
                <div>
                    <flux:heading size="lg">Acceso al correo</flux:heading>
                    <flux:subheading class="mt-2">
                        Elige cómo deseas acceder a tu buzón corporativo.
                    </flux:subheading>
                </div>

                <div class="grid gap-3">
                    <a
                        href="{{ $webmailUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-start gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-cyan-200 hover:bg-cyan-50/50"
                        data-test="frontend-webmail-link"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-cyan-100 text-cyan-700">
                            <flux:icon name="globe-alt" class="size-5" />
                        </span>
                        <span>
                            <span class="block font-semibold text-slate-900">Abrir Webmail</span>
                            <span class="mt-1 block text-sm text-slate-600">
                                Ingresa directamente al correo web de Titon en una nueva pestaña.
                            </span>
                        </span>
                    </a>

                    <button
                        type="button"
                        class="flex w-full items-start gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-start transition hover:border-cyan-200 hover:bg-cyan-50/50"
                        x-on:click="showOutlook = true"
                        data-test="frontend-outlook-manual-trigger"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-slate-700">
                            <flux:icon name="computer-desktop" class="size-5" />
                        </span>
                        <span>
                            <span class="block font-semibold text-slate-900">Configurar Outlook</span>
                            <span class="mt-1 block text-sm text-slate-600">
                                Abre el manual para configurar tu correo en Outlook de escritorio.
                            </span>
                        </span>
                    </button>
                </div>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cerrar</flux:button>
                    </flux:modal.close>
                </div>
            </div>

            <div x-show="showOutlook" x-cloak class="space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">Manual de Outlook</flux:heading>
                        <flux:subheading class="mt-2">
                            Guía de configuración de correo corporativo en Outlook de escritorio.
                        </flux:subheading>
                    </div>

                    <a
                        href="{{ $outlookManualUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700"
                        data-test="frontend-outlook-manual-pdf-link"
                    >
                        <flux:icon name="arrow-top-right-on-square" class="size-4" />
                        Abrir en nueva pestaña
                    </a>
                </div>

                <iframe
                    src="{{ $outlookManualUrl }}"
                    title="Manual de configuración de Outlook TITON"
                    class="h-[min(70vh,32rem)] w-full rounded-xl border border-slate-200 bg-white"
                    data-test="frontend-outlook-manual-pdf"
                ></iframe>

                <div class="flex items-center justify-between gap-3">
                    <flux:button type="button" variant="ghost" x-on:click="showOutlook = false">
                        Volver
                    </flux:button>

                    <flux:modal.close>
                        <flux:button variant="primary">Cerrar</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </div>
    </flux:modal>
@endonce
