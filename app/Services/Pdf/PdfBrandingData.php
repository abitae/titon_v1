<?php

namespace App\Services\Pdf;

use App\Enums\PdfHeaderLayout;

readonly class PdfBrandingData
{
    public function __construct(
        public string $companyName,
        public ?string $businessName,
        public ?string $ruc,
        public ?string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $logoFilesystemPath,
        public string $primaryColor,
        public string $secondaryColor,
        public bool $showLogo,
        public int $logoWidth,
        public int $logoHeight,
        public string $logoPosition,
        public string $logoVerticalAlign,
        public PdfHeaderLayout $headerLayout,
        public string $headerTextAlign,
        public int $headerPadding,
        public int $titleFontSize,
        public int $metaFontSize,
        public bool $showHeaderRule,
        public int $headerRuleThickness,
        public bool $showCompanyName,
        public bool $showBusinessName,
        public bool $showRuc,
        public bool $showAddress,
        public bool $showPhone,
        public bool $showEmail,
        public ?string $footerText,
        public bool $showFooterBorder,
        public int $footerFontSize,
        public int $marginTop,
        public int $marginBottom,
        public int $marginLeft,
        public int $marginRight,
        public bool $showPageNumbers,
        public bool $showGeneratedAt,
    ) {}

    public function hasHeader(): bool
    {
        return $this->showLogo
            || ($this->showCompanyName && $this->companyName !== '')
            || ($this->showBusinessName && filled($this->businessName))
            || ($this->showRuc && filled($this->ruc));
    }

    public function logoCellWidth(): int
    {
        return max($this->logoWidth + 8, 34);
    }

    public function logoVerticalAlignCss(): string
    {
        return match ($this->logoVerticalAlign) {
            'middle' => 'middle',
            'bottom' => 'bottom',
            default => 'top',
        };
    }

    public function headerTextAlignCss(): string
    {
        return match ($this->headerTextAlign) {
            'center' => 'center',
            'right' => 'right',
            default => 'left',
        };
    }

    public function displayTitle(): string
    {
        if ($this->showBusinessName && filled($this->businessName)) {
            return $this->businessName;
        }

        return $this->companyName;
    }

    /**
     * @return list<string>
     */
    public function metaLines(): array
    {
        $lines = [];

        if ($this->showCompanyName && $this->showBusinessName && filled($this->businessName) && $this->businessName !== $this->companyName) {
            $lines[] = $this->companyName;
        }

        if ($this->showRuc && filled($this->ruc)) {
            $lines[] = 'RUC: '.$this->ruc;
        }

        if ($this->showAddress && filled($this->address)) {
            $lines[] = $this->address;
        }

        if ($this->showPhone && filled($this->phone)) {
            $lines[] = 'Tel: '.$this->phone;
        }

        if ($this->showEmail && filled($this->email)) {
            $lines[] = $this->email;
        }

        return $lines;
    }
}
