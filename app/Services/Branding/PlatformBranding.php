<?php

namespace App\Services\Branding;

use App\Services\Application\ApplicationSettingsManager;

class PlatformBranding
{
    public const DEFAULT_NAME = 'TITON EIRL';

    public const SHORT_LOGO_PATH = 'img/logo.png';

    public const LONG_LOGO_PATH = 'img/logo completo.png';

    public const FAVICON_PATH = 'img/logo.ico';

    public function __construct(
        protected ApplicationSettingsManager $applicationSettings,
    ) {}

    public function name(): string
    {
        return $this->applicationSettings->appName() ?: self::DEFAULT_NAME;
    }

    public function logoUrl(): string
    {
        return $this->shortLogoUrl();
    }

    public function shortLogoUrl(): string
    {
        return $this->applicationSettings->logoUrl() ?: asset(self::SHORT_LOGO_PATH);
    }

    public function longLogoUrl(): string
    {
        return $this->applicationSettings->logoUrl() ?: asset(self::LONG_LOGO_PATH);
    }

    public function faviconUrl(): string
    {
        return $this->applicationSettings->logoUrl() ?: asset(self::FAVICON_PATH);
    }
}
