<?php

use App\Enums\FleetEquipmentOperationalStatus;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Livewire\Mechanics\ManageFleetWorkOrders;
use App\Models\Company;
use App\Models\FleetEquipment;
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

    $this->user->companies()->attach($this->company->id, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => true,
    ]);

    setPermissionsTeamId($this->company->id);
    $this->user->assignRole($this->role);

    $this->actingAs($this->user);
    session([CompanyContext::SESSION_KEY => $this->company->id]);
    setPermissionsTeamId($this->company->id);

    $this->equipment = FleetEquipment::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'internal_code' => 'EQ-WO',
        'equipment_type' => 'Mix',
        'name' => 'Unidad prueba OT',
        'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
    ]);
});

test('kanban move updates work order status', function () {
    $wo = FleetWorkOrder::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'fleet_equipment_id' => $this->equipment->id,
        'code' => 'OT-K-1',
        'type' => FleetWorkOrderType::Preventive->value(),
        'issued_at' => now()->toDateString(),
        'priority' => 'media',
        'status' => FleetWorkOrderStatus::Generated->value(),
        'labor_cost' => 0,
        'spare_parts_cost' => 0,
        'total_cost' => 0,
    ]);

    Livewire::test(ManageFleetWorkOrders::class)
        ->call('kanbanMove', $wo->id, FleetWorkOrderStatus::InProgress->value())
        ->assertHasNoErrors();

    expect($wo->fresh()->status)->toBe(FleetWorkOrderStatus::InProgress->value());
});

test('bulk apply status updates selected work orders', function () {
    $a = FleetWorkOrder::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'fleet_equipment_id' => $this->equipment->id,
        'code' => 'OT-B-1',
        'type' => FleetWorkOrderType::Preventive->value(),
        'issued_at' => now()->toDateString(),
        'priority' => 'media',
        'status' => FleetWorkOrderStatus::Generated->value(),
        'labor_cost' => 0,
        'spare_parts_cost' => 0,
        'total_cost' => 0,
    ]);
    $b = FleetWorkOrder::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'fleet_equipment_id' => $this->equipment->id,
        'code' => 'OT-B-2',
        'type' => FleetWorkOrderType::Corrective->value(),
        'issued_at' => now()->toDateString(),
        'priority' => 'media',
        'status' => FleetWorkOrderStatus::Assigned->value(),
        'labor_cost' => 0,
        'spare_parts_cost' => 0,
        'total_cost' => 0,
    ]);

    Livewire::test(ManageFleetWorkOrders::class)
        ->set('selectedIds', [$a->id, $b->id])
        ->set('bulkTargetStatus', FleetWorkOrderStatus::Observed->value())
        ->call('bulkApplyStatus')
        ->assertHasNoErrors();

    expect($a->fresh()->status)->toBe(FleetWorkOrderStatus::Observed->value());
    expect($b->fresh()->status)->toBe(FleetWorkOrderStatus::Observed->value());
});

test('filtered work orders excel export succeeds', function () {
    $this->get(route('mechanics.report.work-orders.excel', ['status' => 'generada']))
        ->assertSuccessful();
});

test('start create from calendar pre fills scheduled date when opening modal', function () {
    Livewire::test(ManageFleetWorkOrders::class)
        ->call('startCreateFromCalendar', '2026-06-10')
        ->assertSet('showFormModal', true)
        ->assertSet('scheduled_date', '2026-06-10');
});

test('work order form modal opens with compact layout', function () {
    Livewire::test(ManageFleetWorkOrders::class)
        ->call('openCreate')
        ->assertSet('showFormModal', true)
        ->assertSee('Nueva orden de trabajo')
        ->assertSee('Detalle y vinculaciones');
});

test('work order equipment select is searchable', function () {
    Livewire::test(ManageFleetWorkOrders::class)
        ->set('filter_equipment_search', 'EQ-WO')
        ->call('selectFilterFleetEquipment', $this->equipment->id)
        ->assertSet('filter_equipment_id', $this->equipment->id);
});

test('work orders board defaults to graficos tab with chart panels', function () {
    FleetWorkOrder::withoutGlobalScopes()->create([
        'company_id' => $this->company->id,
        'fleet_equipment_id' => $this->equipment->id,
        'code' => 'OT-G-1',
        'type' => FleetWorkOrderType::Preventive->value(),
        'issued_at' => now()->toDateString(),
        'priority' => 'media',
        'status' => FleetWorkOrderStatus::Generated->value(),
        'labor_cost' => 100,
        'spare_parts_cost' => 50,
        'total_cost' => 150,
    ]);

    Livewire::test(ManageFleetWorkOrders::class)
        ->assertSet('viewTab', 'graficos')
        ->assertSee('OT por estado')
        ->assertSee('Composición de costos')
        ->assertSee('data-chart-root', false)
        ->assertSee('data-chart-id="wo-by-status"', false)
        ->call('setTab', 'list')
        ->call('setTab', 'graficos')
        ->assertDispatched('charts-refresh');
});
