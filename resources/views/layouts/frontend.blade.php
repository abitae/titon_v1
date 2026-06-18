@php
    $siteContent = app(\App\Services\Frontend\SiteContentService::class);
    $applicationName = $siteContent->brandName();
    $applicationLogo = $siteContent->brandLogoUrl();
    $brandFaviconUrl = $siteContent->brandFaviconUrl();
    $contactInfo = $siteContent->section('contact.info');
    $pageTitle = filled($title ?? null) ? $title.' - '.$applicationName : $applicationName;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', [
            'title' => $title ?? null,
            'siteName' => $applicationName,
            'faviconUrl' => $brandFaviconUrl,
        ])
    </head>
    <body class="flex min-h-screen flex-col bg-white font-sans text-slate-900 antialiased">
        <header
            x-data="{ open: false }"
            class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur-md"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                    <span class="flex size-10 items-center justify-center overflow-hidden rounded-lg bg-slate-900">
                        @if ($applicationLogo)
                            <img src="{{ $applicationLogo }}" alt="{{ $applicationName }}" class="size-full object-cover" />
                        @else
                            <x-app-logo-icon class="size-6 fill-current text-white" />
                        @endif
                    </span>
                    <span class="text-lg font-semibold tracking-tight text-slate-900">{{ $applicationName }}</span>
                </a>

                <nav class="hidden items-center gap-8 md:flex">
                    <a
                        href="{{ route('home') }}"
                        @class([
                            'text-sm font-medium transition hover:text-cyan-700',
                            'text-cyan-700' => request()->routeIs('home'),
                            'text-slate-600' => ! request()->routeIs('home'),
                        ])
                        wire:navigate
                    >
                        Inicio
                    </a>
                    <a
                        href="{{ route('frontend.about') }}"
                        @class([
                            'text-sm font-medium transition hover:text-cyan-700',
                            'text-cyan-700' => request()->routeIs('frontend.about'),
                            'text-slate-600' => ! request()->routeIs('frontend.about'),
                        ])
                        wire:navigate
                    >
                        Nosotros
                    </a>
                    <a
                        href="{{ route('frontend.projects') }}"
                        @class([
                            'text-sm font-medium transition hover:text-cyan-700',
                            'text-cyan-700' => request()->routeIs('frontend.projects'),
                            'text-slate-600' => ! request()->routeIs('frontend.projects'),
                        ])
                        wire:navigate
                    >
                        Proyectos
                    </a>
                    <a
                        href="{{ route('frontend.contact') }}"
                        @class([
                            'text-sm font-medium transition hover:text-cyan-700',
                            'text-cyan-700' => request()->routeIs('frontend.contact'),
                            'text-slate-600' => ! request()->routeIs('frontend.contact'),
                        ])
                        wire:navigate
                    >
                        Contacto
                    </a>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    @auth
                        <flux:button :href="route('dashboard')" variant="primary" wire:navigate>
                            Panel
                        </flux:button>
                    @else
                        <flux:button :href="route('login')" variant="primary" wire:navigate>
                            Acceder
                        </flux:button>
                    @endauth
                </div>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 md:hidden"
                    @click="open = !open"
                    aria-label="Menú"
                >
                    <flux:icon name="bars-3" class="size-6" />
                </button>
            </div>

            <div
                x-show="open"
                x-cloak
                class="border-t border-slate-200 bg-white px-4 py-4 md:hidden"
            >
                <nav class="flex flex-col gap-3">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-slate-700" wire:navigate>Inicio</a>
                    <a href="{{ route('frontend.about') }}" class="text-sm font-medium text-slate-700" wire:navigate>Nosotros</a>
                    <a href="{{ route('frontend.projects') }}" class="text-sm font-medium text-slate-700" wire:navigate>Proyectos</a>
                    <a href="{{ route('frontend.contact') }}" class="text-sm font-medium text-slate-700" wire:navigate>Contacto</a>
                    @auth
                        <flux:button :href="route('dashboard')" variant="primary" class="w-full" wire:navigate>Panel</flux:button>
                    @else
                        <flux:button :href="route('login')" variant="primary" class="w-full" wire:navigate>Acceder</flux:button>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 bg-slate-900 text-slate-300">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div>
                    <p class="text-lg font-semibold text-white">{{ $applicationName }}</p>
                    <p class="mt-3 text-sm leading-relaxed">
                        Infraestructura, obras civiles y servicios integrales con los más altos estándares de calidad.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-white">Enlaces</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white" wire:navigate>Inicio</a></li>
                        <li><a href="{{ route('frontend.about') }}" class="hover:text-white" wire:navigate>Nosotros</a></li>
                        <li><a href="{{ route('frontend.projects') }}" class="hover:text-white" wire:navigate>Proyectos</a></li>
                        <li><a href="{{ route('frontend.contact') }}" class="hover:text-white" wire:navigate>Contacto</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-white">Contacto</p>
                    @if ($contactInfo)
                        <div class="mt-4 space-y-2 text-sm">
                            @if ($contactInfo->subtitle)
                                <p>{{ $contactInfo->subtitle }}</p>
                            @endif
                            @if ($contactInfo->body)
                                @foreach (explode("\n", $contactInfo->body) as $line)
                                    <p>{{ $line }}</p>
                                @endforeach
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ $applicationName }}. Todos los derechos reservados.
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
