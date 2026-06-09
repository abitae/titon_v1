<?php

use App\Livewire\Purchases\ManagePurchaseRequests;
use App\Livewire\Settings\ManageCostTypes;
use App\Models\Company;
use App\Models\CostType;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->user = User::factory()->create();
    $this->role = Role::findByName('Super Admin', 'web');

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

    $this->project = Project::factory()->create([
        'company_id' => $this->company->id,
        'responsible_user_id' => $this->user->id,
    ]);
});

test('cost types page renders for authorized users', function () {
    $this->get(route('settings.cost-types'))
        ->assertOk()
        ->assertSee('Tipos de costo');
});

test('cost types can be created updated and deleted', function () {
    Livewire::test(ManageCostTypes::class)
        ->call('openCreateModal')
        ->set('name', 'Mano de obra')
        ->set('code', 'MO')
        ->set('description', 'Costos de personal')
        ->set('sort_order', 1)
        ->call('saveCostType')
        ->assertHasNoErrors();

    $costType = CostType::query()->where('name', 'Mano de obra')->firstOrFail();

    $this->assertDatabaseHas('cost_types', [
        'company_id' => $this->company->id,
        'name' => 'Mano de obra',
        'code' => 'MO',
    ]);

    Livewire::test(ManageCostTypes::class)
        ->call('openEditModal', $costType->id)
        ->set('name', 'Mano de obra directa')
        ->call('saveCostType')
        ->assertHasNoErrors();

    expect($costType->fresh()->name)->toBe('Mano de obra directa');

    Livewire::test(ManageCostTypes::class)
        ->call('deleteCostType', $costType->id);

    $this->assertDatabaseMissing('cost_types', ['id' => $costType->id]);
});

test('purchase request stores cost type at requirement level', function () {
    $costType = CostType::factory()->create([
        'company_id' => $this->company->id,
        'name' => 'Material',
        'code' => 'MAT',
    ]);

    Livewire::test(ManagePurchaseRequests::class)
        ->call('openCreateModal')
        ->call('openItemModal')
        ->set('item_product_or_service', 'Cable THW')
        ->set('item_unit', 'rollo')
        ->set('item_quantity', '5')
        ->set('item_cost_center_ua', 'UA-001')
        ->call('saveItem')
        ->assertHasNoErrors()
        ->set('work_project_id', $this->project->id)
        ->set('requested_by', $this->user->id)
        ->set('priority', 'alta')
        ->set('request_date', now()->toDateString())
        ->set('description', 'Compra con centro de costo')
        ->set('cost_type_id', (string) $costType->id)
        ->call('savePurchaseRequest')
        ->assertHasNoErrors();

    $requirement = PurchaseRequest::query()->where('description', 'Compra con centro de costo')->firstOrFail();

    $this->assertDatabaseHas('requirements', [
        'id' => $requirement->id,
        'cost_type_id' => $costType->id,
    ]);

    $this->assertDatabaseHas('requirement_items', [
        'requirement_id' => $requirement->id,
        'cost_center_ua' => 'UA-001',
        'description' => 'Cable THW',
    ]);

    expect($requirement->items()->first()->cost_center_ua)->toBe('UA-001');
});
