<?php

use App\Models\Company;
use App\Models\ContractPaymentSchedule;
use App\Models\Document;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use App\Services\Dashboard\DashboardAnalytics;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->role = Role::findByName('Super Admin', 'web');

    $this->companyA = Company::factory()->create([
        'name' => 'Empresa A',
        'business_name' => 'Empresa A SAC',
    ]);

    $this->companyB = Company::factory()->create([
        'name' => 'Empresa B',
        'business_name' => 'Empresa B SAC',
    ]);

    foreach ([$this->companyA, $this->companyB] as $index => $company) {
        $this->user->companies()->attach($company, [
            'role_id' => $this->role->id,
            'active' => true,
            'default_company' => $index === 0,
        ]);
    }

    setPermissionsTeamId($this->companyA->id);
    $this->user->assignRole($this->role);
    $this->actingAs($this->user);
    session([CompanyContext::SESSION_KEY => $this->companyA->id]);
    setPermissionsTeamId($this->companyA->id);

    $this->projectA = Project::factory()->create([
        'company_id' => $this->companyA->id,
        'responsible_user_id' => $this->user->id,
        'city' => 'Lima',
        'status' => 'en_ejecucion',
    ]);

    $this->projectB = Project::factory()->create([
        'company_id' => $this->companyB->id,
        'responsible_user_id' => $this->user->id,
        'city' => 'Cusco',
        'status' => 'planificada',
    ]);

    $this->supplierA = Supplier::factory()->create([
        'company_id' => $this->companyA->id,
        'business_name' => 'Proveedor Alpha',
    ]);

    $this->supplierB = Supplier::factory()->create([
        'company_id' => $this->companyB->id,
        'business_name' => 'Proveedor Beta',
    ]);

    $this->orderA = PurchaseOrder::factory()->create([
        'company_id' => $this->companyA->id,
        'work_project_id' => $this->projectA->id,
        'supplier_id' => $this->supplierA->id,
        'total' => 12000,
        'subtotal' => 10000,
        'tax' => 2000,
        'currency' => 'PEN',
    ]);

    $this->orderB = PurchaseOrder::factory()->create([
        'company_id' => $this->companyB->id,
        'work_project_id' => $this->projectB->id,
        'supplier_id' => $this->supplierB->id,
        'total' => 7000,
        'subtotal' => 6000,
        'tax' => 1000,
        'currency' => 'PEN',
    ]);

    $this->contractA = SupplierContract::factory()->create([
        'company_id' => $this->companyA->id,
        'work_project_id' => $this->projectA->id,
        'supplier_id' => $this->supplierA->id,
        'order_id' => $this->orderA->id,
        'total_amount' => 12000,
        'currency' => 'PEN',
        'status' => 'en_ejecucion',
    ]);

    $this->contractB = SupplierContract::factory()->create([
        'company_id' => $this->companyB->id,
        'work_project_id' => $this->projectB->id,
        'supplier_id' => $this->supplierB->id,
        'order_id' => $this->orderB->id,
        'total_amount' => 7000,
        'currency' => 'PEN',
        'status' => 'aprobado',
    ]);

    ContractPaymentSchedule::factory()->create([
        'company_id' => $this->companyA->id,
        'supplier_contract_id' => $this->contractA->id,
        'scheduled_amount' => 3000,
        'paid_amount' => 1000,
        'balance' => 2000,
        'status' => 'vencido',
        'due_date' => now()->subDays(4),
    ]);

    ContractPaymentSchedule::factory()->create([
        'company_id' => $this->companyB->id,
        'supplier_contract_id' => $this->contractB->id,
        'scheduled_amount' => 2000,
        'paid_amount' => 0,
        'balance' => 2000,
        'status' => 'pendiente',
        'due_date' => now()->subDays(2),
    ]);

    SupplierPayment::factory()->create([
        'company_id' => $this->companyA->id,
        'work_project_id' => $this->projectA->id,
        'supplier_contract_id' => $this->contractA->id,
        'supplier_id' => $this->supplierA->id,
        'contract_payment_schedule_id' => null,
        'responsible_user_id' => $this->user->id,
        'amount' => 1000,
        'currency' => 'PEN',
        'payment_date' => now()->subMonth(),
    ]);

    SupplierPayment::factory()->create([
        'company_id' => $this->companyB->id,
        'work_project_id' => $this->projectB->id,
        'supplier_contract_id' => $this->contractB->id,
        'supplier_id' => $this->supplierB->id,
        'contract_payment_schedule_id' => null,
        'responsible_user_id' => $this->user->id,
        'amount' => 500,
        'currency' => 'PEN',
        'payment_date' => now()->subDays(5),
    ]);

    PurchaseRequest::factory()->create([
        'company_id' => $this->companyA->id,
        'work_project_id' => $this->projectA->id,
        'requested_by' => $this->user->id,
        'status' => 'borrador',
    ]);

    PurchaseRequest::factory()->create([
        'company_id' => $this->companyB->id,
        'work_project_id' => $this->projectB->id,
        'requested_by' => $this->user->id,
        'status' => 'en_cotizacion',
    ]);

    Document::factory()->create([
        'company_id' => $this->companyA->id,
        'work_project_id' => $this->projectA->id,
        'created_by_user_id' => $this->user->id,
        'current_user_id' => $this->user->id,
        'status' => 'derivado',
        'due_date' => now()->subDay(),
    ]);

    Document::factory()->create([
        'company_id' => $this->companyB->id,
        'work_project_id' => $this->projectB->id,
        'created_by_user_id' => $this->user->id,
        'current_user_id' => $this->user->id,
        'status' => 'vencido',
        'due_date' => now()->subDays(3),
    ]);
});

test('dashboard analytics resolves active company mode', function () {
    $analytics = app(DashboardAnalytics::class)->build($this->user, 'company');

    expect($analytics['mode'])->toBe('company');
    expect($analytics['scope_label'])->toContain('Empresa A');
    expect($analytics['kpis']['active_projects'])->toBe(1);
    expect($analytics['kpis']['contracted_total'])->toBe(12000.0);
    expect($analytics['kpis']['paid_total'])->toBe(1000.0);
    expect($analytics['kpis']['pending_requests'])->toBe(1);
    expect($analytics['kpis']['expired_documents'])->toBe(1);
});

test('dashboard analytics can build consolidated gerencial view', function () {
    $analytics = app(DashboardAnalytics::class)->build($this->user, 'consolidated');

    expect($analytics['mode'])->toBe('consolidated');
    expect($analytics['can_view_consolidated'])->toBeTrue();
    expect($analytics['kpis']['active_projects'])->toBe(2);
    expect($analytics['kpis']['contracted_total'])->toBe(19000.0);
    expect($analytics['kpis']['paid_total'])->toBe(1500.0);
    expect($analytics['kpis']['overdue_payments'])->toBe(2);
    expect($analytics['charts']['projects_by_city']['data']['labels'])->toContain('Lima', 'Cusco');
});

test('dashboard executive report can be downloaded as pdf', function () {
    $response = $this->get(route('reports.dashboard.pdf', ['mode' => 'consolidated']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
