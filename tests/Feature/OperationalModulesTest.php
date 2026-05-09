<?php

use App\Enums\CatalogType;
use App\Enums\ProjectStatus;
use App\Enums\SupplierStatus;
use App\Livewire\Projects\ManageProjects;
use App\Livewire\Settings\ManageCatalogs;
use App\Livewire\Suppliers\ManageSuppliers;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->role = Role::findByName('Administrador', 'web');

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

test('projects module can create records for the active company', function () {
    CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::City->value(),
        'name' => 'Lima',
    ]);

    Livewire::test(ManageProjects::class)
        ->call('openCreateModal')
        ->set('code', 'OBR-001')
        ->set('name', 'Obra Central')
        ->set('city', 'Lima')
        ->set('client_name', 'Cliente Demo')
        ->set('estimated_budget', '250000')
        ->set('status', ProjectStatus::Planned->value())
        ->call('saveProject')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('projects', [
        'company_id' => $this->company->id,
        'code' => 'OBR-001',
        'name' => 'Obra Central',
    ]);
});

test('project queries respect the active company scope', function () {
    $otherCompany = Company::factory()->create();

    Project::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Obra Visible',
    ]);

    Project::factory()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Obra Oculta',
    ]);

    Livewire::test(ManageProjects::class)
        ->assertSee('Obra Visible')
        ->assertDontSee('Obra Oculta');
});

test('suppliers module can filter by city and status', function () {
    Supplier::factory()->create([
        'company_id' => $this->company->id,
        'business_name' => 'Proveedor Lima',
        'city' => 'Lima',
        'status' => SupplierStatus::Active->value(),
    ]);

    Supplier::factory()->create([
        'company_id' => $this->company->id,
        'business_name' => 'Proveedor Cusco',
        'city' => 'Cusco',
        'status' => SupplierStatus::Inactive->value(),
    ]);

    Livewire::test(ManageSuppliers::class)
        ->set('cityFilter', 'Lima')
        ->set('statusFilter', SupplierStatus::Active->value())
        ->assertSee('Proveedor Lima')
        ->assertDontSee('Proveedor Cusco');
});

test('catalogs module can create company scoped items', function () {
    Livewire::test(ManageCatalogs::class)
        ->set('selectedType', CatalogType::Bank->value())
        ->call('openCreateModal')
        ->set('name', 'BCP')
        ->set('code', 'BCP')
        ->set('description', 'Banco principal')
        ->set('sort_order', 1)
        ->call('saveCatalogItem')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('catalog_items', [
        'company_id' => $this->company->id,
        'type' => CatalogType::Bank->value(),
        'name' => 'BCP',
    ]);
});

test('operational module pages render for authorized users', function () {
    $this->get(route('modules.projects'))->assertOk()->assertSee('Obras');
    $this->get(route('modules.suppliers'))->assertOk()->assertSee('Proveedores');
    $this->get(route('settings.catalogs'))->assertOk()->assertSee('Configuración general');
});
