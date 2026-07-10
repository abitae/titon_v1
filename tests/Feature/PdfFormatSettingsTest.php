<?php

use App\Livewire\Settings\ManagePdfFormats;
use App\Models\Company;
use App\Models\CompanyPdfSetting;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use App\Services\Pdf\PdfBrandingResolver;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create([
        'logo' => 'companies/logos/test-logo.png',
        'primary_color' => '#112233',
        'secondary_color' => '#445566',
    ]);

    Storage::fake('public');
    Storage::disk('public')->put('companies/logos/test-logo.png', 'fake-image');

    $this->user = User::factory()->create();
    $this->role = Role::findByName('Administrador', 'web');

    Permission::findOrCreate('pdf-formats.ver', 'web');
    Permission::findOrCreate('pdf-formats.editar', 'web');
    $this->role->givePermissionTo(['pdf-formats.ver', 'pdf-formats.editar']);

    $this->user->companies()->attach($this->company, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($this->company->id);
    $this->user->assignRole($this->role);

    $this->actingAs($this->user);
    session([CompanyContext::SESSION_KEY => $this->company->id]);
    setPermissionsTeamId($this->company->id);
});

test('pdf formats settings page is reachable', function () {
    $this->get(route('settings.pdf-formats'))
        ->assertOk()
        ->assertSee('Formatos PDF')
        ->assertSee('Empresa a personalizar')
        ->assertSee('Disenos preestablecidos')
        ->assertSee('Corporativo')
        ->assertSee('Moderno')
        ->assertSee('Legal')
        ->assertSee('Restablecer')
        ->assertSee('Vista previa')
        ->assertSee('iframe', false);
});

test('pdf format settings can select which company is being customized', function () {
    $otherCompany = Company::factory()->create([
        'name' => 'Empresa Norte',
        'primary_color' => '#663399',
        'secondary_color' => '#339966',
    ]);

    $this->user->companies()->attach($otherCompany, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => false,
    ]);

    CompanyPdfSetting::query()->create([
        'company_id' => $otherCompany->id,
        ...CompanyPdfSetting::defaultAttributes(),
        'header_layout' => 'banner',
        'logo_position' => 'right',
        'footer_text' => 'Formato Norte',
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->assertSet('selected_company_id', $this->company->id)
        ->set('selected_company_id', $otherCompany->id)
        ->assertSet('header_layout', 'banner')
        ->assertSet('logo_position', 'right')
        ->assertSet('footer_text', 'Formato Norte')
        ->assertSet('primary_color', '#663399')
        ->assertSet('secondary_color', '#339966');
});

test('pdf format preview uses draft settings from livewire form', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->set('header_layout', 'banner')
        ->set('logo_width', 48)
        ->set('logo_height', 22)
        ->set('logo_position', 'right')
        ->set('logo_vertical_align', 'middle')
        ->set('header_text_align', 'center')
        ->set('header_padding', 12)
        ->set('title_font_size', 16)
        ->set('meta_font_size', 10)
        ->set('show_header_rule', false)
        ->set('footer_font_size', 11)
        ->set('show_footer_border', false)
        ->set('primary_color', '#ff0000')
        ->call('refreshPreviewFrame')
        ->assertSet('previewIframeUrl', fn (string $url): bool => str_contains($url, 'settings/pdf-formats/preview'));

    $draft = session('pdf_format_preview_draft');

    expect($draft)->toBeArray()
        ->and($draft['company_id'])->toBe($this->company->id)
        ->and($draft['header_layout'])->toBe('banner')
        ->and($draft['logo_width'])->toBe(48)
        ->and($draft['logo_height'])->toBe(22)
        ->and($draft['logo_position'])->toBe('right')
        ->and($draft['logo_vertical_align'])->toBe('middle')
        ->and($draft['header_text_align'])->toBe('center')
        ->and($draft['header_padding'])->toBe(12)
        ->and($draft['title_font_size'])->toBe(16)
        ->and($draft['meta_font_size'])->toBe(10)
        ->and($draft['show_header_rule'])->toBeFalse()
        ->and($draft['footer_font_size'])->toBe(11)
        ->and($draft['show_footer_border'])->toBeFalse()
        ->and($draft['primary_color'])->toBe('#ff0000');

    $response = $this->get(route('settings.pdf-formats.preview', ['preview' => 1]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('pdf format preset can be applied and customized before saving', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->call('applyPreset', 'executive')
        ->assertSet('header_layout', 'banner')
        ->assertSet('logo_position', 'right')
        ->assertSet('logo_vertical_align', 'middle')
        ->set('logo_width', 52)
        ->call('refreshPreviewFrame')
        ->assertSet('previewIframeUrl', fn (string $url): bool => str_contains($url, 'settings/pdf-formats/preview'));

    $draft = session('pdf_format_preview_draft');

    expect($draft)->toBeArray()
        ->and($draft['header_layout'])->toBe('banner')
        ->and($draft['logo_position'])->toBe('right')
        ->and($draft['logo_width'])->toBe(52)
        ->and($draft['show_email'])->toBeTrue();
});

test('additional professional presets can be applied', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->call('applyPreset', 'legal')
        ->assertSet('header_text_align', 'center')
        ->assertSet('margin_left', 22)
        ->call('applyPreset', 'modern')
        ->assertSet('header_layout', 'banner')
        ->assertSet('show_header_rule', false)
        ->call('applyPreset', 'technical')
        ->assertSet('title_font_size', 12)
        ->assertSet('show_email', true);
});

test('pdf format form can be reset to default values', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
        'header_layout' => 'banner',
        'logo_width' => 50,
        'logo_position' => 'right',
        'header_text_align' => 'center',
        'title_font_size' => 18,
        'show_header_rule' => false,
        'show_email' => true,
        'show_footer_border' => false,
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->call('resetToDefaults')
        ->assertSet('header_layout', 'classic')
        ->assertSet('logo_width', 32)
        ->assertSet('logo_height', 16)
        ->assertSet('logo_position', 'left')
        ->assertSet('header_text_align', 'left')
        ->assertSet('title_font_size', 13)
        ->assertSet('show_header_rule', true)
        ->assertSet('show_email', false)
        ->assertSet('show_footer_border', true)
        ->assertSet('primary_color', '#112233')
        ->assertSet('secondary_color', '#445566');
});

test('pdf format preview modal can be opened from settings page', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->call('previewPdf')
        ->assertSet('showPdfModal', true)
        ->assertSeeHtml('iframe');
});

test('pdf formats can be saved for active company', function () {
    Livewire::test(ManagePdfFormats::class)
        ->set('header_layout', 'banner')
        ->set('logo_width', 44)
        ->set('logo_height', 20)
        ->set('logo_position', 'right')
        ->set('logo_vertical_align', 'bottom')
        ->set('header_text_align', 'center')
        ->set('header_padding', 12)
        ->set('title_font_size', 15)
        ->set('meta_font_size', 10)
        ->set('show_header_rule', false)
        ->set('header_rule_thickness', 4)
        ->set('footer_text', 'Documento corporativo Titon')
        ->set('show_footer_border', false)
        ->set('footer_font_size', 10)
        ->set('show_phone', true)
        ->call('save')
        ->assertHasNoErrors();

    $settings = CompanyPdfSetting::query()->where('company_id', $this->company->id)->first();

    expect($settings)->not->toBeNull()
        ->and($settings->header_layout)->toBe('banner')
        ->and($settings->logo_width)->toBe(44)
        ->and($settings->logo_height)->toBe(20)
        ->and($settings->logo_position)->toBe('right')
        ->and($settings->logo_vertical_align)->toBe('bottom')
        ->and($settings->header_text_align)->toBe('center')
        ->and($settings->header_padding)->toBe(12)
        ->and($settings->title_font_size)->toBe(15)
        ->and($settings->meta_font_size)->toBe(10)
        ->and($settings->show_header_rule)->toBeFalse()
        ->and($settings->header_rule_thickness)->toBe(4)
        ->and($settings->footer_text)->toBe('Documento corporativo Titon')
        ->and($settings->show_footer_border)->toBeFalse()
        ->and($settings->footer_font_size)->toBe(10)
        ->and($settings->show_phone)->toBeTrue();
});

test('pdf formats are saved independently per selected company', function () {
    $otherCompany = Company::factory()->create([
        'primary_color' => '#991122',
        'secondary_color' => '#229911',
    ]);

    $this->user->companies()->attach($otherCompany, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => false,
    ]);

    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
        'footer_text' => 'Formato empresa activa',
    ]);

    CompanyPdfSetting::query()->create([
        'company_id' => $otherCompany->id,
        ...CompanyPdfSetting::defaultAttributes(),
        'header_layout' => 'classic',
        'footer_text' => 'Formato de otra empresa',
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->set('selected_company_id', $otherCompany->id)
        ->set('header_layout', 'banner')
        ->set('footer_text', 'Formato empresa seleccionada')
        ->call('save')
        ->assertHasNoErrors();

    expect(CompanyPdfSetting::query()->where('company_id', $this->company->id)->value('footer_text'))->toBe('Formato empresa activa')
        ->and(CompanyPdfSetting::query()->where('company_id', $otherCompany->id)->value('footer_text'))->toBe('Formato empresa seleccionada')
        ->and(CompanyPdfSetting::query()->where('company_id', $otherCompany->id)->value('header_layout'))->toBe('banner');
});

test('pdf format preview uses selected company draft', function () {
    $otherCompany = Company::factory()->create([
        'name' => 'Empresa Sur',
        'primary_color' => '#123abc',
        'secondary_color' => '#abc123',
    ]);

    $this->user->companies()->attach($otherCompany, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => false,
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->set('selected_company_id', $otherCompany->id)
        ->set('header_layout', 'banner')
        ->set('footer_text', 'Vista Sur')
        ->call('refreshPreviewFrame');

    $draft = session('pdf_format_preview_draft');
    $branding = app(PdfBrandingResolver::class)->resolveForPreview($this->user);

    expect($draft['company_id'])->toBe($otherCompany->id)
        ->and($branding->companyName)->toBe('Empresa Sur')
        ->and($branding->footerText)->toBe('Vista Sur')
        ->and($branding->primaryColor)->toBe('#123abc');
});

test('pdf branding resolver uses company logo path', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    $branding = app(PdfBrandingResolver::class)->resolve($this->user);

    expect($branding->logoFilesystemPath)->not->toBeNull()
        ->and($branding->showLogo)->toBeTrue()
        ->and($branding->logoWidth)->toBe(32)
        ->and($branding->logoHeight)->toBe(16)
        ->and($branding->logoPosition)->toBe('left')
        ->and($branding->headerTextAlign)->toBe('left')
        ->and($branding->showFooterBorder)->toBeTrue()
        ->and($branding->primaryColor)->toBe('#112233');
});

test('pdf format preview route returns a pdf', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    $response = $this->get(route('settings.pdf-formats.preview'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->headers->get('cache-control'))->toContain('no-store');
    expect($response->getContent())->toStartWith('%PDF');
});

test('pdf header partial does not render css blocks as visible header text', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    $branding = app(PdfBrandingResolver::class)->resolve($this->user);

    $html = view('reports.pdf.partials.mpdf-header', [
        'branding' => $branding,
    ])->render();

    expect($html)->not->toContain('<style')
        ->and($html)->not->toContain('.pdf-header-classic')
        ->and($html)->not->toContain('.pdf-header-banner');
});
