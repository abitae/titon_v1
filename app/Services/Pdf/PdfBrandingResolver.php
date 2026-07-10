<?php

namespace App\Services\Pdf;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Enums\PdfHeaderLayout;
use App\Models\Company;
use App\Models\CompanyPdfSetting;
use App\Models\User;
use App\Services\Application\ApplicationSettingsManager;
use Illuminate\Support\Facades\Storage;

class PdfBrandingResolver
{
    public function __construct(
        protected ResolveCurrentCompany $resolveCurrentCompany,
        protected ApplicationSettingsManager $applicationSettings,
    ) {}

    public function resolve(?User $actor = null): PdfBrandingData
    {
        $company = $this->resolveCurrentCompany->handle($actor ?? auth()->user());

        if ($company === null) {
            return $this->fallbackBranding();
        }

        $settings = CompanyPdfSetting::query()->firstOrCreate(
            ['company_id' => $company->id],
            CompanyPdfSetting::defaultAttributes(),
        );

        return $this->fromCompany($company, $settings);
    }

    public function resolveForCompany(Company $company): PdfBrandingData
    {
        $settings = CompanyPdfSetting::query()->firstOrCreate(
            ['company_id' => $company->id],
            CompanyPdfSetting::defaultAttributes(),
        );

        return $this->fromCompany($company, $settings);
    }

    public function resolveForPreview(?User $actor = null): PdfBrandingData
    {
        $company = $this->resolveCurrentCompany->handle($actor ?? auth()->user());

        if ($company === null) {
            return $this->fallbackBranding();
        }

        /** @var array<string, mixed>|null $draft */
        $draft = session('pdf_format_preview_draft');

        if (is_array($draft) && (int) ($draft['company_id'] ?? 0) === $company->id) {
            return $this->fromDraft($company, $draft);
        }

        return $this->resolveForCompany($company);
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    protected function fromDraft(Company $company, array $draft): PdfBrandingData
    {
        return new PdfBrandingData(
            companyName: $company->name,
            businessName: $company->business_name,
            ruc: $company->ruc,
            address: $company->address,
            phone: $company->phone,
            email: $company->email,
            logoFilesystemPath: $this->resolveLogoPath($company),
            primaryColor: filled($draft['primary_color'] ?? null) ? (string) $draft['primary_color'] : ($company->primary_color ?? '#0f172a'),
            secondaryColor: filled($draft['secondary_color'] ?? null) ? (string) $draft['secondary_color'] : ($company->secondary_color ?? '#0891b2'),
            showLogo: (bool) ($draft['show_logo'] ?? true),
            headerLayout: PdfHeaderLayout::tryFrom((string) ($draft['header_layout'] ?? 'classic')) ?? PdfHeaderLayout::Classic,
            showCompanyName: (bool) ($draft['show_company_name'] ?? true),
            showBusinessName: (bool) ($draft['show_business_name'] ?? true),
            showRuc: (bool) ($draft['show_ruc'] ?? true),
            showAddress: (bool) ($draft['show_address'] ?? true),
            showPhone: (bool) ($draft['show_phone'] ?? false),
            showEmail: (bool) ($draft['show_email'] ?? false),
            footerText: filled($draft['footer_text'] ?? null) ? (string) $draft['footer_text'] : null,
            marginTop: (int) ($draft['margin_top'] ?? 32),
            marginBottom: (int) ($draft['margin_bottom'] ?? 16),
            marginLeft: (int) ($draft['margin_left'] ?? 12),
            marginRight: (int) ($draft['margin_right'] ?? 12),
            showPageNumbers: (bool) ($draft['show_page_numbers'] ?? true),
            showGeneratedAt: (bool) ($draft['show_generated_at'] ?? true),
        );
    }

    protected function fromCompany(Company $company, CompanyPdfSetting $settings): PdfBrandingData
    {
        return new PdfBrandingData(
            companyName: $company->name,
            businessName: $company->business_name,
            ruc: $company->ruc,
            address: $company->address,
            phone: $company->phone,
            email: $company->email,
            logoFilesystemPath: $this->resolveLogoPath($company),
            primaryColor: $settings->primary_color ?? $company->primary_color ?? '#0f172a',
            secondaryColor: $settings->secondary_color ?? $company->secondary_color ?? '#0891b2',
            showLogo: (bool) $settings->show_logo,
            headerLayout: PdfHeaderLayout::tryFrom((string) $settings->header_layout) ?? PdfHeaderLayout::Classic,
            showCompanyName: (bool) $settings->show_company_name,
            showBusinessName: (bool) $settings->show_business_name,
            showRuc: (bool) $settings->show_ruc,
            showAddress: (bool) $settings->show_address,
            showPhone: (bool) $settings->show_phone,
            showEmail: (bool) $settings->show_email,
            footerText: $settings->footer_text,
            marginTop: (int) $settings->margin_top,
            marginBottom: (int) $settings->margin_bottom,
            marginLeft: (int) $settings->margin_left,
            marginRight: (int) $settings->margin_right,
            showPageNumbers: (bool) $settings->show_page_numbers,
            showGeneratedAt: (bool) $settings->show_generated_at,
        );
    }

    protected function resolveLogoPath(Company $company): ?string
    {
        $companyPath = $this->filesystemPathFromStoredValue($company->logo);

        if ($companyPath !== null) {
            return $companyPath;
        }

        return $this->filesystemPathFromStoredValue($this->applicationSettings->logoPath());
    }

    protected function filesystemPathFromStoredValue(?string $storedValue): ?string
    {
        if (blank($storedValue) || filter_var($storedValue, FILTER_VALIDATE_URL)) {
            return null;
        }

        $path = Storage::disk('public')->path($storedValue);

        return is_file($path) ? $path : null;
    }

    protected function fallbackBranding(): PdfBrandingData
    {
        return new PdfBrandingData(
            companyName: $this->applicationSettings->appName() ?? config('app.name'),
            businessName: null,
            ruc: null,
            address: null,
            phone: null,
            email: null,
            logoFilesystemPath: $this->filesystemPathFromStoredValue($this->applicationSettings->logoPath()),
            primaryColor: '#0f172a',
            secondaryColor: '#0891b2',
            showLogo: true,
            headerLayout: PdfHeaderLayout::Classic,
            showCompanyName: true,
            showBusinessName: false,
            showRuc: false,
            showAddress: false,
            showPhone: false,
            showEmail: false,
            footerText: null,
            marginTop: 32,
            marginBottom: 16,
            marginLeft: 12,
            marginRight: 12,
            showPageNumbers: true,
            showGeneratedAt: true,
        );
    }
}
