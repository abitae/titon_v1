@php
    $branding = app(\App\Services\Branding\PlatformBranding::class);
    $applicationName = $siteName ?? $branding->name();
    $resolvedFaviconUrl = $faviconUrl ?? $branding->faviconUrl();
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.$applicationName : $applicationName }}
</title>

@if ($resolvedFaviconUrl)
    <link rel="icon" href="{{ $resolvedFaviconUrl }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ $resolvedFaviconUrl }}">
@endif

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
