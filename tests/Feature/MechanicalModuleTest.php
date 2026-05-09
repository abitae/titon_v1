<?php

use App\Enums\FleetEquipmentOperationalStatus;
use App\Enums\FleetSparePartMovementDirection;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Livewire\Mechanics\ManageFleetEquipments;
use App\Livewire\Mechanics\ManageFleetSpareParts;
use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\FleetSparePart;
use App\Models\FleetWorkOrder;
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

test('equipment list is scoped by active company', function () {
    $other = Company::factory()->create();

    FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-A',
        'equipment_type' => 'Camion',
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

test('equipment can be created via livewire', function () {
    Livewire::test(ManageFleetEquipments::class)
        ->call('openCreateModal')
        ->set('internal_code', 'EQ-001')
        ->set('equipment_type', 'Retro')
        ->set('name', 'Unidad demo')
        ->set('operational_status', FleetEquipmentOperationalStatus::Operational->value())
        ->call('saveEquipment')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('fleet_equipments', [
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-001',
        'name' => 'Unidad demo',
    ]);
});

test('spare outbound movement updates stock and work order spare parts cost', function () {
    $equipment = FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-SP',
        'equipment_type' => 'Mix',
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
