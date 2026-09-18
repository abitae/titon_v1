<img
    src="{{ app(\App\Services\Branding\PlatformBranding::class)->shortLogoUrl() }}"
    alt="{{ app(\App\Services\Branding\PlatformBranding::class)->name() }}"
    {{ $attributes->merge(['class' => 'object-contain']) }}
/>
