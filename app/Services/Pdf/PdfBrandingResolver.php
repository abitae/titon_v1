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
        $user = $actor ?? auth()->user();
        $company = $user instanceof User
            ? $this->resolveCurrentCompany->handle($user)
            : null;

        if ($company === null) {
            return $this->fallbackBranding();
        }

        /** @var array<string, mixed>|null $draft */
        $draft = session('pdf_format_preview_draft');

        if (is_array($draft) && $user instanceof User) {
            $draftCompanyId = (int) ($draft['company_id'] ?? 0);
            $draftCompany = $this->previewCompanyForUser($user, $draftCompanyId);

            if ($draftCompany instanceof Company) {
                return $this->fromDraft($draftCompany, $draft);
            }
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
            logoWidth: (int) ($draft['logo_width'] ?? 32),
            logoHeight: (int) ($draft['logo_height'] ?? 16),
            logoPosition: in_array(($draft['logo_position'] ?? 'left'), ['left', 'right'], true) ? (string) $draft['logo_position'] : 'left',
            logoVerticalAlign: in_array(($draft['logo_vertical_align'] ?? 'top'), ['top', 'middle', 'bottom'], true) ? (string) $draft['logo_vertical_align'] : 'top',
            headerLayout: PdfHeaderLayout::tryFrom((string) ($draft['header_layout'] ?? 'classic')) ?? PdfHeaderLayout::Classic,
            headerTextAlign: in_array(($draft['header_text_align'] ?? 'left'), ['left', 'center', 'right'], true) ? (string) $draft['header_text_align'] : 'left',
            headerPadding: (int) ($draft['header_padding'] ?? 8),
            titleFontSize: (int) ($draft['title_font_size'] ?? 13),
            metaFontSize: (int) ($draft['meta_font_size'] ?? 9),
            showHeaderRule: (bool) ($draft['show_header_rule'] ?? true),
            headerRuleThickness: (int) ($draft['header_rule_thickness'] ?? 2),
            showCompanyName: (bool) ($draft['show_company_name'] ?? true),
            showBusinessName: (bool) ($draft['show_business_name'] ?? true),
            showRuc: (bool) ($draft['show_ruc'] ?? true),
            showAddress: (bool) ($draft['show_address'] ?? true),
            showPhone: (bool) ($draft['show_phone'] ?? false),
            showEmail: (bool) ($draft['show_email'] ?? false),
            footerText: filled($draft['footer_text'] ?? null) ? (string) $draft['footer_text'] : null,
            showFooterBorder: (bool) ($draft['show_footer_border'] ?? true),
            footerFontSize: (int) ($draft['footer_font_size'] ?? 9),
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
            logoWidth: (int) $settings->logo_width,
            logoHeight: (int) $settings->logo_height,
            logoPosition: in_array($settings->logo_position, ['left', 'right'], true) ? $settings->logo_position : 'left',
            logoVerticalAlign: in_array($settings->logo_vertical_align, ['top', 'middle', 'bottom'], true) ? $settings->logo_vertical_align : 'top',
            headerLayout: PdfHeaderLayout::tryFrom((string) $settings->header_layout) ?? PdfHeaderLayout::Classic,
            headerTextAlign: in_array($settings->header_text_align, ['left', 'center', 'right'], true) ? $settings->header_text_align : 'left',
            headerPadding: (int) $settings->header_padding,
            titleFontSize: (int) $settings->title_font_size,
            metaFontSize: (int) $settings->meta_font_size,
            showHeaderRule: (bool) $settings->show_header_rule,
            headerRuleThickness: (int) $settings->header_rule_thickness,
            showCompanyName: (bool) $settings->show_company_name,
            showBusinessName: (bool) $settings->show_business_name,
            showRuc: (bool) $settings->show_ruc,
            showAddress: (bool) $settings->show_address,
            showPhone: (bool) $settings->show_phone,
            showEmail: (bool) $settings->show_email,
            footerText: $settings->footer_text,
            showFooterBorder: (bool) $settings->show_footer_border,
            footerFontSize: (int) $settings->footer_font_size,
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

    protected function previewCompanyForUser(User $user, int $companyId): ?Company
    {
        if ($companyId <= 0) {
            return null;
        }

        /** @var Company|null $company */
        $company = $user->activeCompanies()
            ->whereKey($companyId)
            ->first();

        return $company;
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
            logoWidth: 32,
            logoHeight: 16,
            logoPosition: 'left',
            logoVerticalAlign: 'top',
            headerLayout: PdfHeaderLayout::Classic,
            headerTextAlign: 'left',
            headerPadding: 8,
            titleFontSize: 13,
            metaFontSize: 9,
            showHeaderRule: true,
            headerRuleThickness: 2,
            showCompanyName: true,
            showBusinessName: false,
            showRuc: false,
            showAddress: false,
            showPhone: false,
            showEmail: false,
            footerText: null,
            showFooterBorder: true,
            footerFontSize: 9,
            marginTop: 32,
            marginBottom: 16,
            marginLeft: 12,
            marginRight: 12,
            showPageNumbers: true,
            showGeneratedAt: true,
        );
    }
}
