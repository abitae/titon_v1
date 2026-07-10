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
        ->assertSee('Vista previa')
        ->assertSee('iframe', false);
});

test('pdf format preview uses draft settings from livewire form', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    Livewire::test(ManagePdfFormats::class)
        ->set('header_layout', 'banner')
        ->set('primary_color', '#ff0000')
        ->call('refreshPreviewFrame')
        ->assertSet('previewIframeUrl', fn (string $url): bool => str_contains($url, 'settings/pdf-formats/preview'));

    $draft = session('pdf_format_preview_draft');

    expect($draft)->toBeArray()
        ->and($draft['company_id'])->toBe($this->company->id)
        ->and($draft['header_layout'])->toBe('banner')
        ->and($draft['primary_color'])->toBe('#ff0000');

    $response = $this->get(route('settings.pdf-formats.preview', ['preview' => 1]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
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
        ->set('footer_text', 'Documento corporativo Titon')
        ->set('show_phone', true)
        ->call('save')
        ->assertHasNoErrors();

    $settings = CompanyPdfSetting::query()->where('company_id', $this->company->id)->first();

    expect($settings)->not->toBeNull()
        ->and($settings->header_layout)->toBe('banner')
        ->and($settings->footer_text)->toBe('Documento corporativo Titon')
        ->and($settings->show_phone)->toBeTrue();
});

test('pdf branding resolver uses company logo path', function () {
    CompanyPdfSetting::query()->create([
        'company_id' => $this->company->id,
        ...CompanyPdfSetting::defaultAttributes(),
    ]);

    $branding = app(PdfBrandingResolver::class)->resolve($this->user);

    expect($branding->logoFilesystemPath)->not->toBeNull()
        ->and($branding->showLogo)->toBeTrue()
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
    expect($response->getContent())->toStartWith('%PDF');
});
