<?php

namespace App\Services\Branding;

use App\Services\Application\ApplicationSettingsManager;

class PlatformBranding
{
    public function __construct(
        protected ApplicationSettingsManager $applicationSettings,
    ) {}

    public function name(): string
    {
        return $this->applicationSettings->appName();
    }

    public function logoUrl(): ?string
    {
        return $this->applicationSettings->logoUrl();
    }

    public function faviconUrl(): ?string
    {
        return $this->applicationSettings->logoUrl();
    }
}
