@props([
    'fullWidth' => false,
])

@php
    $webmailUrl = config('frontend.webmail_url');
    $mailHost = config('frontend.mail_host');
    $imapPort = config('frontend.imap_port');
    $smtpPort = config('frontend.smtp_port');
    $mailDomain = config('frontend.mail_domain');
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
    <flux:modal name="frontend-mail-access" class="max-w-lg">
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
                                Consulta el manual para configurar tu correo en Outlook de escritorio.
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

            <div x-show="showOutlook" x-cloak class="space-y-6">
                <div>
                    <flux:heading size="lg">Outlook de escritorio</flux:heading>
                    <flux:subheading class="mt-2">
                        Sigue estos pasos para configurar tu cuenta corporativa con IMAP.
                    </flux:subheading>
                </div>

                <ol class="list-decimal space-y-3 ps-5 text-sm leading-relaxed text-slate-700">
                    <li>Abre Outlook y ve a <strong>Archivo → Agregar cuenta</strong>.</li>
                    <li>Ingresa tu dirección de correo corporativa (por ejemplo, <strong>usuario@{{ $mailDomain }}</strong>).</li>
                    <li>Selecciona <strong>Configuración avanzada</strong> y elige <strong>Configurar manualmente</strong> o <strong>IMAP</strong>.</li>
                    <li>
                        <strong>Servidor entrante (IMAP):</strong> {{ $mailHost }}, puerto {{ $imapPort }}, cifrado SSL/TLS.
                    </li>
                    <li>
                        <strong>Servidor saliente (SMTP):</strong> {{ $mailHost }}, puerto {{ $smtpPort }}, cifrado SSL/TLS.
                    </li>
                    <li><strong>Usuario:</strong> tu correo completo.</li>
                    <li><strong>Contraseña:</strong> la de tu buzón de correo (cPanel), no la del panel de la aplicación.</li>
                    <li>Haz clic en <strong>Probar configuración de cuenta</strong> y luego en <strong>Finalizar</strong>.</li>
                </ol>

                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    Si tienes problemas, verifica que la contraseña sea la del buzón cPanel y que tu cuenta esté activa.
                </p>

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
