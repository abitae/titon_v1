<?php

use App\Enums\CatalogType;
use App\Enums\FleetEquipmentOperationalStatus;
use App\Enums\FleetSparePartMovementDirection;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Livewire\Mechanics\ManageFleetEquipments;
use App\Livewire\Mechanics\ManageFleetEquipmentTypes;
use App\Livewire\Mechanics\ManageFleetSpareParts;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\FleetSparePart;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use App\Models\Project;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create(['correlative_prefix' => 'TITON']);
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

    $this->equipmentType = CatalogItem::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::EquipmentType->value(),
        'name' => 'Retroexcavadora',
        'code' => 'RETRO',
        'is_active' => true,
    ]);
});

test('equipment list is scoped by active company', function () {
    $other = Company::factory()->create();

    FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-A',
        'equipment_type_id' => $this->equipmentType->id,
        'equipment_type' => $this->equipmentType->name,
        'name' => 'Visible',
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);

    FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $other->id,
        'internal_code' => 'EQ-B',
        'equipment_type' => 'Camion',
        'name' => 'Oculto',
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);

    Livewire::test(ManageFleetEquipments::class)
        ->assertSee('Visible')
        ->assertDontSee('Oculto');
});

test('equipment can be assigned to a work project and filtered by obra', function () {
    $projectA = Project::factory()->create([
        'company_id' => $this->company->id,
        'code' => 'OBR-A',
        'name' => 'Obra Norte',
    ]);

    $projectB = Project::factory()->create([
        'company_id' => $this->company->id,
        'code' => 'OBR-B',
        'name' => 'Obra Sur',
    ]);

    FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-NORTE',
        'equipment_type_id' => $this->equipmentType->id,
        'equipment_type' => $this->equipmentType->name,
        'name' => 'Equipo en norte',
        'work_project_id' => $projectA->id,
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);

    FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-SUR',
        'equipment_type_id' => $this->equipmentType->id,
        'equipment_type' => $this->equipmentType->name,
        'name' => 'Equipo en sur',
        'work_project_id' => $projectB->id,
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);

    Livewire::test(ManageFleetEquipments::class)
        ->set('projectFilter', (string) $projectA->id)
        ->assertSee('Equipo en norte')
        ->assertSee('OBR-A')
        ->assertDontSee('Equipo en sur');

    Livewire::test(ManageFleetEquipments::class)
        ->set('search', 'Obra Sur')
        ->assertSee('Equipo en sur')
        ->assertDontSee('Equipo en norte');

    Livewire::test(ManageFleetEquipments::class)
        ->call('openCreateModal')
        ->set('equipment_type_id', $this->equipmentType->id)
        ->set('name', 'Equipo nueva obra')
        ->set('work_project_id', $projectA->id)
        ->set('operational_status', FleetEquipmentOperationalStatus::Operational->value())
        ->call('saveEquipment')
        ->assertHasNoErrors();

    $equipment = FleetEquipment::withoutGlobalScopes()
        ->where('company_id', $this->company->id)
        ->where('name', 'Equipo nueva obra')
        ->first();

    expect($equipment)->not->toBeNull()
        ->and($equipment->work_project_id)->toBe($projectA->id);
});

test('equipment save validates required fields with spanish messages', function () {
    Livewire::test(ManageFleetEquipments::class)
        ->call('openCreateModal')
        ->set('equipment_type_id', null)
        ->set('name', '')
        ->call('saveEquipment')
        ->assertHasErrors([
            'equipment_type_id' => 'required',
            'name' => 'required',
        ]);
});

test('equipment create modal warns when no equipment types exist', function () {
    CatalogItem::query()
        ->ofType(CatalogType::EquipmentType)
        ->update(['is_active' => false]);

    Livewire::test(ManageFleetEquipments::class)
        ->call('openCreateModal')
        ->assertSet('showFormModal', false);
});

test('equipment type save validates required name', function () {
    Livewire::test(ManageFleetEquipmentTypes::class)
        ->call('openCreate')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('equipment can be created via livewire with auto generated code', function () {
    Livewire::test(ManageFleetEquipments::class)
        ->call('openCreateModal')
        ->set('equipment_type_id', $this->equipmentType->id)
        ->set('name', 'Unidad demo')
        ->set('operational_status', FleetEquipmentOperationalStatus::Operational->value())
        ->call('saveEquipment')
        ->assertHasNoErrors();

    $equipment = FleetEquipment::withoutGlobalScopes()->where('company_id', $this->company->id)->first();

    expect($equipment)->not->toBeNull()
        ->and($equipment->name)->toBe('Unidad demo')
        ->and($equipment->equipment_type_id)->toBe($this->equipmentType->id)
        ->and($equipment->internal_code)->not->toBe('');
});

test('equipment detail shows technical inspection history', function () {
    $equipment = FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-HIST',
        'equipment_type_id' => $this->equipmentType->id,
        'equipment_type' => $this->equipmentType->name,
        'name' => 'Equipo historial',
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);

    FleetTechnicalInspection::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'fleet_equipment_id' => $equipment->id,
        'reviewed_at' => '2024-01-15',
        'due_at' => '2025-01-15',
        'result' => 'Aprobado',
        'inspection_center' => 'Centro A',
        'status' => 'vigente',
    ]);

    Livewire::test(ManageFleetEquipments::class)
        ->call('openDetailModal', $equipment->id)
        ->assertSee('Historial de revisiones tecnicas')
        ->assertSee('15/01/2024')
        ->assertSee('Centro A');
});

test('equipment type can be managed via mechanics catalog crud', function () {
    Livewire::test(ManageFleetEquipmentTypes::class)
        ->call('openCreate')
        ->set('name', 'Camion volquete')
        ->set('code', 'CAM')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('catalog_items', [
        'company_id' => $this->company->id,
        'type' => CatalogType::EquipmentType->value(),
        'name' => 'Camion volquete',
    ]);
});

test('spare outbound movement updates stock and work order spare parts cost', function () {
    $equipment = FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-SP',
        'equipment_type_id' => $this->equipmentType->id,
        'equipment_type' => $this->equipmentType->name,
        'name' => 'Mixer',
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);

    $part = FleetSparePart::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'code' => 'R001',
        'name' => 'Filtro',
        'unit' => 'und',
        'stock_quantity' => 5,
        'min_stock' => 1,
        'unit_cost' => 10,
        'status' => 'activo',
    ]);

    $order = FleetWorkOrder::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'fleet_equipment_id' => $equipment->id,
        'code' => 'OT-TEST-1',
        'type' => FleetWorkOrderType::Corrective->value(),
        'issued_at' => now()->toDateString(),
        'priority' => 'media',
        'status' => FleetWorkOrderStatus::Generated->value(),
        'labor_cost' => 0,
        'spare_parts_cost' => 0,
        'total_cost' => 0,
    ]);

    Livewire::test(ManageFleetSpareParts::class)
        ->call('openMovement', $part->id)
        ->set('movement_direction', FleetSparePartMovementDirection::Outbound->value())
        ->set('movement_quantity', '2')
        ->set('movement_work_order_id', $order->id)
        ->call('saveMovement')
        ->assertHasNoErrors();

    $part->refresh();
    expect((float) $part->stock_quantity)->toBe(3.0);

    $order->refresh();
    expect((float) $order->spare_parts_cost)->toBe(20.0);
});

test('mechanics maintenance cost excel report downloads for authorized user', function () {
    $this->get(route('mechanics.report.maintenance-costs.excel'))->assertSuccessful();
});

test('mechanics pdf reports can be previewed inline for modal viewer', function () {
    $response = $this->get(route('mechanics.report.equipments.pdf', ['preview' => 1]));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');
    expect(str_contains(strtolower((string) $response->headers->get('content-disposition')), 'inline'))->toBeTrue();
    expect(str_starts_with($response->getContent(), '%PDF'))->toBeTrue();
});

test('equipments page opens pdf report in modal', function () {
    $component = Livewire::test(ManageFleetEquipments::class)
        ->call('openEquipmentsReportPdf')
        ->assertSet('showPdfModal', true)
        ->assertSet('pdfViewerTitle', 'Equipos y maquinarias');

    expect($component->get('pdfViewerUrl'))->toContain('preview=1');
});
