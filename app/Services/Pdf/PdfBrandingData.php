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
        public PdfHeaderLayout $headerLayout,
        public bool $showCompanyName,
        public bool $showBusinessName,
        public bool $showRuc,
        public bool $showAddress,
        public bool $showPhone,
        public bool $showEmail,
        public ?string $footerText,
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
