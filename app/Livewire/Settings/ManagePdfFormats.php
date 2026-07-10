<?php

namespace App\Livewire\Settings;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPdfInModal;
use App\Enums\PdfHeaderLayout;
use App\Models\Company;
use App\Models\CompanyPdfSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ManagePdfFormats extends Component
{
    use InteractsWithToast, ViewsPdfInModal;

    public string $previewIframeUrl = '';

    public string $title = 'Formatos PDF';

    public int $selected_company_id = 0;

    public bool $show_logo = true;

    public int $logo_width = 32;

    public int $logo_height = 16;

    public string $logo_position = 'left';

    public string $logo_vertical_align = 'top';

    public string $header_layout = 'classic';

    public string $header_text_align = 'left';

    public int $header_padding = 8;

    public int $title_font_size = 13;

    public int $meta_font_size = 9;

    public bool $show_header_rule = true;

    public int $header_rule_thickness = 2;

    public bool $show_company_name = true;

    public bool $show_business_name = true;

    public bool $show_ruc = true;

    public bool $show_address = true;

    public bool $show_phone = false;

    public bool $show_email = false;

    public string $primary_color = '';

    public string $secondary_color = '';

    public string $footer_text = '';

    public bool $show_footer_border = true;

    public int $footer_font_size = 9;

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

        $this->selected_company_id = (int) $company->id;

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

        $selectedCompany = $this->selectedCompany() ?? $company;

        return view('livewire.settings.manage-pdf-formats', [
            'company' => $selectedCompany,
            'availableCompanies' => $this->availableCompanies(),
            'layoutOptions' => PdfHeaderLayout::cases(),
            'presetOptions' => $this->presetOptions(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function selectCompany(): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.ver'), 403);

        $company = $this->selectedCompany();
        abort_if($company === null, 403);

        $settings = CompanyPdfSetting::query()->firstOrCreate(
            ['company_id' => $company->id],
            CompanyPdfSetting::defaultAttributes(),
        );

        $this->fillFromSettings($settings, $company);
        $this->refreshPreviewFrame($company);
    }

    public function updatedSelectedCompanyId(): void
    {
        $this->selectCompany();
    }

    public function applyPreset(string $preset): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.editar'), 403);

        $company = $this->selectedCompany();
        abort_if($company === null, 403);

        $attributes = $this->presetAttributes($preset, $company);

        if ($attributes === []) {
            $this->warningToast('No se encontro el diseno seleccionado.', 'Diseno no disponible');

            return;
        }

        $this->fillFromArray($attributes);
        $this->refreshPreviewFrame($company);

        $this->successToast('Puedes ajustar cualquier campo antes de guardar.', 'Diseno aplicado');
    }

    public function resetToDefaults(): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.editar'), 403);

        $company = $this->selectedCompany();
        abort_if($company === null, 403);

        $this->fillFromArray($this->defaultFormAttributes($company));
        $this->refreshPreviewFrame($company);

        $this->successToast('Valores estandar restaurados. Guarda para hacerlos permanentes.', 'Formato restablecido');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.editar'), 403);

        $company = $this->selectedCompany();
        abort_if($company === null, 403);

        $validated = $this->validateWithToastFeedback([
            'show_logo' => ['required', 'boolean'],
            'logo_width' => ['required', 'integer', 'min:16', 'max:80'],
            'logo_height' => ['required', 'integer', 'min:8', 'max:40'],
            'logo_position' => ['required', 'in:left,right'],
            'logo_vertical_align' => ['required', 'in:top,middle,bottom'],
            'header_layout' => ['required', 'in:'.implode(',', PdfHeaderLayout::values())],
            'header_text_align' => ['required', 'in:left,center,right'],
            'header_padding' => ['required', 'integer', 'min:4', 'max:18'],
            'title_font_size' => ['required', 'integer', 'min:10', 'max:20'],
            'meta_font_size' => ['required', 'integer', 'min:7', 'max:13'],
            'show_header_rule' => ['required', 'boolean'],
            'header_rule_thickness' => ['required', 'integer', 'min:1', 'max:5'],
            'show_company_name' => ['required', 'boolean'],
            'show_business_name' => ['required', 'boolean'],
            'show_ruc' => ['required', 'boolean'],
            'show_address' => ['required', 'boolean'],
            'show_phone' => ['required', 'boolean'],
            'show_email' => ['required', 'boolean'],
            'primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'show_footer_border' => ['required', 'boolean'],
            'footer_font_size' => ['required', 'integer', 'min:7', 'max:12'],
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
            'logo_width' => 'ancho del logo',
            'logo_height' => 'alto del logo',
            'logo_position' => 'ubicacion del logo',
            'logo_vertical_align' => 'alineacion del logo',
            'header_text_align' => 'alineacion del encabezado',
            'header_padding' => 'espaciado del encabezado',
            'title_font_size' => 'tamano del titulo',
            'meta_font_size' => 'tamano de datos',
            'header_rule_thickness' => 'grosor de linea',
            'footer_font_size' => 'tamano del pie',
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

        $this->successToast('Formato PDF guardado para '.$company->name.'.', 'Configuracion actualizada');

        session()->forget('pdf_format_preview_draft');
        $this->refreshPreviewFrame($company);
    }

    public function previewPdf(): void
    {
        abort_unless(auth()->user()?->can('pdf-formats.ver'), 403);

        $company = $this->selectedCompany();
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
        $company ??= $this->selectedCompany() ?? app(ResolveCurrentCompany::class)->handle(auth()->user());

        if ($company === null) {
            $this->previewIframeUrl = '';

            return;
        }

        $this->persistPreviewDraft($company);

        $this->previewIframeUrl = route('settings.pdf-formats.preview', absolute: false).'?preview=1&ts='.now()->timestamp;
    }

    protected function fillFromSettings(CompanyPdfSetting $settings, Company $company): void
    {
        $this->fillFromArray([
            'show_logo' => (bool) $settings->show_logo,
            'logo_width' => (int) $settings->logo_width,
            'logo_height' => (int) $settings->logo_height,
            'logo_position' => (string) $settings->logo_position,
            'logo_vertical_align' => (string) $settings->logo_vertical_align,
            'header_layout' => (string) $settings->header_layout,
            'header_text_align' => (string) $settings->header_text_align,
            'header_padding' => (int) $settings->header_padding,
            'title_font_size' => (int) $settings->title_font_size,
            'meta_font_size' => (int) $settings->meta_font_size,
            'show_header_rule' => (bool) $settings->show_header_rule,
            'header_rule_thickness' => (int) $settings->header_rule_thickness,
            'show_company_name' => (bool) $settings->show_company_name,
            'show_business_name' => (bool) $settings->show_business_name,
            'show_ruc' => (bool) $settings->show_ruc,
            'show_address' => (bool) $settings->show_address,
            'show_phone' => (bool) $settings->show_phone,
            'show_email' => (bool) $settings->show_email,
            'primary_color' => $settings->primary_color ?? $company->primary_color ?? '#0f172a',
            'secondary_color' => $settings->secondary_color ?? $company->secondary_color ?? '#0891b2',
            'footer_text' => (string) ($settings->footer_text ?? ''),
            'show_footer_border' => (bool) $settings->show_footer_border,
            'footer_font_size' => (int) $settings->footer_font_size,
            'margin_top' => (int) $settings->margin_top,
            'margin_bottom' => (int) $settings->margin_bottom,
            'margin_left' => (int) $settings->margin_left,
            'margin_right' => (int) $settings->margin_right,
            'show_page_numbers' => (bool) $settings->show_page_numbers,
            'show_generated_at' => (bool) $settings->show_generated_at,
        ]);
    }

    /**
     * @return Collection<int, Company>
     */
    protected function availableCompanies(): Collection
    {
        return auth()->user()?->activeCompanies()
            ->withPivot(['role_id', 'active', 'default_company'])
            ->orderByDesc('company_user.default_company')
            ->orderBy('companies.name')
            ->get()
            ?? collect();
    }

    protected function selectedCompany(): ?Company
    {
        if ($this->selected_company_id <= 0) {
            return null;
        }

        /** @var Company|null $company */
        $company = $this->availableCompanies()->firstWhere('id', $this->selected_company_id);

        return $company;
    }

    protected function persistPreviewDraft(Company $company): void
    {
        session([
            'pdf_format_preview_draft' => [
                'company_id' => $company->id,
                'show_logo' => $this->show_logo,
                'logo_width' => $this->logo_width,
                'logo_height' => $this->logo_height,
                'logo_position' => $this->logo_position,
                'logo_vertical_align' => $this->logo_vertical_align,
                'header_layout' => $this->header_layout,
                'header_text_align' => $this->header_text_align,
                'header_padding' => $this->header_padding,
                'title_font_size' => $this->title_font_size,
                'meta_font_size' => $this->meta_font_size,
                'show_header_rule' => $this->show_header_rule,
                'header_rule_thickness' => $this->header_rule_thickness,
                'show_company_name' => $this->show_company_name,
                'show_business_name' => $this->show_business_name,
                'show_ruc' => $this->show_ruc,
                'show_address' => $this->show_address,
                'show_phone' => $this->show_phone,
                'show_email' => $this->show_email,
                'primary_color' => $this->primary_color !== '' ? $this->primary_color : null,
                'secondary_color' => $this->secondary_color !== '' ? $this->secondary_color : null,
                'footer_text' => $this->footer_text !== '' ? $this->footer_text : null,
                'show_footer_border' => $this->show_footer_border,
                'footer_font_size' => $this->footer_font_size,
                'margin_top' => $this->margin_top,
                'margin_bottom' => $this->margin_bottom,
                'margin_left' => $this->margin_left,
                'margin_right' => $this->margin_right,
                'show_page_numbers' => $this->show_page_numbers,
                'show_generated_at' => $this->show_generated_at,
            ],
        ]);
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    protected function presetOptions(): array
    {
        return [
            'corporate' => [
                'label' => 'Corporativo',
                'description' => 'Encabezado clasico, logo sobrio y datos legales completos.',
            ],
            'executive' => [
                'label' => 'Ejecutivo',
                'description' => 'Banner superior, logo a la derecha y espacio amplio para reportes.',
            ],
            'compact' => [
                'label' => 'Compacto',
                'description' => 'Membrete ligero para documentos extensos y tablas densas.',
            ],
            'formal' => [
                'label' => 'Formal',
                'description' => 'Presentacion documental con margenes amplios y pie institucional.',
            ],
            'minimal' => [
                'label' => 'Minimalista',
                'description' => 'Encabezado discreto para documentos limpios y de bajo ruido visual.',
            ],
            'technical' => [
                'label' => 'Tecnico',
                'description' => 'Formato compacto para tablas, inventarios y reportes operativos.',
            ],
            'legal' => [
                'label' => 'Legal',
                'description' => 'Margenes amplios, datos completos y lectura formal.',
            ],
            'modern' => [
                'label' => 'Moderno',
                'description' => 'Banner visual con mayor presencia de marca.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function presetAttributes(string $preset, Company $company): array
    {
        return match ($preset) {
            'corporate' => [
                ...$this->defaultFormAttributes($company),
                'logo_width' => 34,
                'logo_height' => 18,
                'footer_text' => 'Documento corporativo '.$company->name,
            ],
            'executive' => [
                ...$this->defaultFormAttributes($company),
                'header_layout' => 'banner',
                'logo_width' => 30,
                'logo_height' => 16,
                'logo_position' => 'right',
                'logo_vertical_align' => 'middle',
                'show_phone' => true,
                'show_email' => true,
                'margin_top' => 36,
                'margin_bottom' => 18,
                'footer_text' => 'Reporte ejecutivo '.$company->name,
            ],
            'compact' => [
                ...$this->defaultFormAttributes($company),
                'logo_width' => 24,
                'logo_height' => 12,
                'show_business_name' => false,
                'show_address' => false,
                'margin_top' => 24,
                'margin_bottom' => 12,
                'margin_left' => 10,
                'margin_right' => 10,
                'footer_text' => '',
            ],
            'formal' => [
                ...$this->defaultFormAttributes($company),
                'logo_width' => 40,
                'logo_height' => 22,
                'logo_vertical_align' => 'middle',
                'show_phone' => true,
                'show_email' => true,
                'margin_top' => 42,
                'margin_bottom' => 22,
                'margin_left' => 18,
                'margin_right' => 18,
                'footer_text' => 'Documento emitido por '.$company->name,
            ],
            'minimal' => [
                ...$this->defaultFormAttributes($company),
                'logo_width' => 22,
                'logo_height' => 10,
                'header_text_align' => 'right',
                'show_business_name' => false,
                'show_address' => false,
                'show_phone' => false,
                'show_email' => false,
                'show_header_rule' => false,
                'show_footer_border' => false,
                'margin_top' => 22,
                'margin_bottom' => 10,
                'footer_text' => '',
            ],
            'technical' => [
                ...$this->defaultFormAttributes($company),
                'header_layout' => 'classic',
                'logo_width' => 28,
                'logo_height' => 14,
                'header_padding' => 6,
                'title_font_size' => 12,
                'meta_font_size' => 8,
                'show_business_name' => false,
                'show_phone' => true,
                'show_email' => true,
                'margin_top' => 26,
                'margin_bottom' => 12,
                'margin_left' => 10,
                'margin_right' => 10,
                'footer_text' => 'Reporte tecnico '.$company->name,
            ],
            'legal' => [
                ...$this->defaultFormAttributes($company),
                'logo_width' => 36,
                'logo_height' => 18,
                'header_text_align' => 'center',
                'header_padding' => 10,
                'title_font_size' => 14,
                'meta_font_size' => 9,
                'show_phone' => true,
                'show_email' => true,
                'margin_top' => 46,
                'margin_bottom' => 24,
                'margin_left' => 22,
                'margin_right' => 22,
                'footer_font_size' => 8,
                'footer_text' => 'Documento legal '.$company->name,
            ],
            'modern' => [
                ...$this->defaultFormAttributes($company),
                'header_layout' => 'banner',
                'logo_width' => 26,
                'logo_height' => 14,
                'logo_position' => 'right',
                'header_text_align' => 'left',
                'header_padding' => 12,
                'title_font_size' => 15,
                'meta_font_size' => 9,
                'show_phone' => true,
                'show_email' => true,
                'show_header_rule' => false,
                'footer_text' => 'Exportacion '.$company->name,
            ],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultFormAttributes(Company $company): array
    {
        return [
            ...CompanyPdfSetting::defaultAttributes(),
            'primary_color' => $company->primary_color ?? '#0f172a',
            'secondary_color' => $company->secondary_color ?? '#0891b2',
            'footer_text' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function fillFromArray(array $attributes): void
    {
        foreach ($attributes as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }
}
