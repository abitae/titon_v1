<?php

namespace App\Livewire\Settings;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPdfInModal;
use App\Enums\PdfHeaderLayout;
use App\Models\Company;
use App\Models\CompanyPdfSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManagePdfFormats extends Component
{
    use InteractsWithToast, ViewsPdfInModal;

    public string $previewIframeUrl = '';

    public string $title = 'Formatos PDF';

    public bool $show_logo = true;

    public string $header_layout = 'classic';

    public bool $show_company_name = true;

    public bool $show_business_name = true;

    public bool $show_ruc = true;

    public bool $show_address = true;

    public bool $show_phone = false;

    public bool $show_email = false;

    public string $primary_color = '';

    public string $secondary_color = '';

    public string $footer_text = '';

    public int $margin_top = 32;

    public int $margin_bottom = 16;

    public int $margin_left = 12;

    public int $margin_right = 12;

    public bool $show_page_numbers = true;

    public bool $show_generated_at = true;

    public function mount(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.ver'), 403);

        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $settings = CompanyPdfSetting::query()->firstOrCreate(
            ['company_id' => $company->id],
            CompanyPdfSetting::defaultAttributes(),
        );

        $this->fillFromSettings($settings, $company);
        $this->refreshPreviewFrame($company);
    }

    public function render(ResolveCurrentCompany $resolveCurrentCompany): View
    {
        abort_unless(auth()->user()?->can('pdf-formats.ver'), 403);

        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        return view('livewire.settings.manage-pdf-formats', [
            'company' => $company,
            'layoutOptions' => PdfHeaderLayout::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function save(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.editar'), 403);

        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $validated = $this->validateWithToastFeedback([
            'show_logo' => ['required', 'boolean'],
            'header_layout' => ['required', 'in:'.implode(',', PdfHeaderLayout::values())],
            'show_company_name' => ['required', 'boolean'],
            'show_business_name' => ['required', 'boolean'],
            'show_ruc' => ['required', 'boolean'],
            'show_address' => ['required', 'boolean'],
            'show_phone' => ['required', 'boolean'],
            'show_email' => ['required', 'boolean'],
            'primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'margin_top' => ['required', 'integer', 'min:20', 'max:60'],
            'margin_bottom' => ['required', 'integer', 'min:10', 'max:40'],
            'margin_left' => ['required', 'integer', 'min:8', 'max:30'],
            'margin_right' => ['required', 'integer', 'min:8', 'max:30'],
            'show_page_numbers' => ['required', 'boolean'],
            'show_generated_at' => ['required', 'boolean'],
        ], [
            'primary_color.regex' => 'El color primario debe tener formato hexadecimal (#RRGGBB).',
            'secondary_color.regex' => 'El color secundario debe tener formato hexadecimal (#RRGGBB).',
        ], [
            'footer_text' => 'texto de pie de pagina',
            'margin_top' => 'margen superior',
            'margin_bottom' => 'margen inferior',
        ]);

        CompanyPdfSetting::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                ...$validated,
                'primary_color' => $validated['primary_color'] !== '' ? $validated['primary_color'] : null,
                'secondary_color' => $validated['secondary_color'] !== '' ? $validated['secondary_color'] : null,
                'footer_text' => $validated['footer_text'] !== '' ? $validated['footer_text'] : null,
            ],
        );

        $this->successToast('Formato PDF guardado para la empresa activa.', 'Configuracion actualizada');

        session()->forget('pdf_format_preview_draft');
        $this->refreshPreviewFrame($company);
    }

    public function previewPdf(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.ver'), 403);

        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $this->persistPreviewDraft($company);
        $this->refreshPreviewFrame($company);

        $this->openRoutePdfModal(
            'settings.pdf-formats.preview',
            'Vista previa del formato PDF',
            [],
            'Membrete según la configuración actual del formulario.',
        );
    }

    public function refreshPreviewFrame(?Company $company = null): void
    {
        $company ??= app(ResolveCurrentCompany::class)->handle(auth()->user());

        if ($company === null) {
            $this->previewIframeUrl = '';

            return;
        }

        $this->persistPreviewDraft($company);

        $this->previewIframeUrl = route('settings.pdf-formats.preview', absolute: false).'?preview=1&ts='.now()->timestamp;
    }

    protected function fillFromSettings(CompanyPdfSetting $settings, Company $company): void
    {
        $this->show_logo = (bool) $settings->show_logo;
        $this->header_layout = (string) $settings->header_layout;
        $this->show_company_name = (bool) $settings->show_company_name;
        $this->show_business_name = (bool) $settings->show_business_name;
        $this->show_ruc = (bool) $settings->show_ruc;
        $this->show_address = (bool) $settings->show_address;
        $this->show_phone = (bool) $settings->show_phone;
        $this->show_email = (bool) $settings->show_email;
        $this->primary_color = $settings->primary_color ?? $company->primary_color ?? '#0f172a';
        $this->secondary_color = $settings->secondary_color ?? $company->secondary_color ?? '#0891b2';
        $this->footer_text = (string) ($settings->footer_text ?? '');
        $this->margin_top = (int) $settings->margin_top;
        $this->margin_bottom = (int) $settings->margin_bottom;
        $this->margin_left = (int) $settings->margin_left;
        $this->margin_right = (int) $settings->margin_right;
        $this->show_page_numbers = (bool) $settings->show_page_numbers;
        $this->show_generated_at = (bool) $settings->show_generated_at;
    }

    protected function persistPreviewDraft(Company $company): void
    {
        session([
            'pdf_format_preview_draft' => [
                'company_id' => $company->id,
                'show_logo' => $this->show_logo,
                'header_layout' => $this->header_layout,
                'show_company_name' => $this->show_company_name,
                'show_business_name' => $this->show_business_name,
                'show_ruc' => $this->show_ruc,
                'show_address' => $this->show_address,
                'show_phone' => $this->show_phone,
                'show_email' => $this->show_email,
                'primary_color' => $this->primary_color !== '' ? $this->primary_color : null,
                'secondary_color' => $this->secondary_color !== '' ? $this->secondary_color : null,
                'footer_text' => $this->footer_text !== '' ? $this->footer_text : null,
                'margin_top' => $this->margin_top,
                'margin_bottom' => $this->margin_bottom,
                'margin_left' => $this->margin_left,
                'margin_right' => $this->margin_right,
                'show_page_numbers' => $this->show_page_numbers,
                'show_generated_at' => $this->show_generated_at,
            ],
        ]);
    }
}
