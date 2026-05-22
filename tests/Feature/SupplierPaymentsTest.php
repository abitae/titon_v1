<?php

use App\Enums\CatalogType;
use App\Livewire\Payments\ManagePaymentSchedules;
use App\Livewire\Payments\ManageSupplierPayments;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\ContractPaymentSchedule;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    $this->supplier = Supplier::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $this->contract = SupplierContract::query()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'supplier_id' => $this->supplier->id,
        'order_id' => PurchaseOrder::factory()->create([
            'company_id' => $this->company->id,
            'work_project_id' => $this->project->id,
            'supplier_id' => $this->supplier->id,
        ])->id,
        'contract_number' => 'CT-200',
        'contract_type' => 'Servicio',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'total_amount' => 5000,
        'currency' => 'PEN',
        'status' => 'aprobado',
    ]);

    $this->operationType = CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::OperationType->value(),
        'name' => 'Pago parcial',
    ]);

    $this->paymentMethod = CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::PaymentMethod->value(),
        'name' => 'Transferencia',
    ]);

    $this->bank = CatalogItem::factory()->create([
        'company_id' => $this->company->id,
        'type' => CatalogType::Bank->value(),
        'name' => 'BCP',
    ]);
});

test('payment schedules can be created for a contract', function () {
    Livewire::test(ManagePaymentSchedules::class, ['supplierContract' => $this->contract])
        ->call('openCreateModal')
        ->set('installment_number', '1')
        ->set('description', 'Primer hito')
        ->set('due_date', now()->addWeek()->toDateString())
        ->set('scheduled_amount', '2500')
        ->call('saveSchedule')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('contract_payment_schedules', [
        'supplier_contract_id' => $this->contract->id,
        'installment_number' => 1,
        'scheduled_amount' => 2500,
    ]);
});

test('supplier payments update schedule balance and status automatically', function () {
    Storage::fake('public');

    $schedule = ContractPaymentSchedule::query()->create([
        'company_id' => $this->company->id,
        'supplier_contract_id' => $this->contract->id,
        'installment_number' => 1,
        'description' => 'Cuota 1',
        'due_date' => now()->addDays(10)->toDateString(),
        'scheduled_amount' => 2000,
        'paid_amount' => 0,
        'balance' => 2000,
        'status' => 'pendiente',
    ]);

    Livewire::test(ManageSupplierPayments::class)
        ->call('openCreateModal')
        ->set('supplier_contract_id', $this->contract->id)
        ->set('supplier_id', $this->supplier->id)
        ->set('work_project_id', $this->project->id)
        ->set('contract_payment_schedule_id', $schedule->id)
        ->set('payment_date', now()->toDateString())
        ->set('amount', '800')
        ->set('currency', 'PEN')
        ->set('operation_type_id', $this->operationType->id)
        ->set('payment_method_id', $this->paymentMethod->id)
        ->set('bank_id', $this->bank->id)
        ->set('operation_number', 'OP-001')
        ->set('responsible_user_id', $this->user->id)
        ->set('concept', 'Pago del primer hito')
        ->set('voucher', [UploadedFile::fake()->create('voucher.pdf', 100, 'application/pdf')])
        ->call('savePayment')
        ->assertHasNoErrors();

    $schedule->refresh();

    expect((float) $schedule->paid_amount)->toBe(800.0);
    expect((float) $schedule->balance)->toBe(1200.0);
    expect($schedule->status)->toBe('parcial');
    expect($this->contract->fresh()->pendingBalance())->toBe(4200.0);
});

test('payments cannot exceed pending schedule balance', function () {
    $schedule = ContractPaymentSchedule::query()->create([
        'company_id' => $this->company->id,
        'supplier_contract_id' => $this->contract->id,
        'installment_number' => 2,
        'description' => 'Cuota 2',
        'due_date' => now()->addDays(10)->toDateString(),
        'scheduled_amount' => 1000,
        'paid_amount' => 0,
        'balance' => 1000,
        'status' => 'pendiente',
    ]);

    Livewire::test(ManageSupplierPayments::class)
        ->call('openCreateModal')
        ->set('supplier_contract_id', $this->contract->id)
        ->set('supplier_id', $this->supplier->id)
        ->set('work_project_id', $this->project->id)
        ->set('contract_payment_schedule_id', $schedule->id)
        ->set('payment_date', now()->toDateString())
        ->set('amount', '1500')
        ->set('currency', 'PEN')
        ->set('responsible_user_id', $this->user->id)
        ->set('concept', 'Pago excedido')
        ->call('savePayment')
        ->assertHasErrors(['amount']);
});

test('payments cannot use records from another company', function () {
    $otherCompany = Company::factory()->create();
    $otherUser = User::factory()->create();

    $otherUser->companies()->attach($otherCompany, [
        'role_id' => $this->role->id,
        'active' => true,
        'default_company' => true,
    ]);

    $otherProject = Project::factory()->create([
        'company_id' => $otherCompany->id,
        'responsible_user_id' => $otherUser->id,
    ]);

    $otherSupplier = Supplier::factory()->create([
        'company_id' => $otherCompany->id,
    ]);

    $otherContract = SupplierContract::query()->create([
        'company_id' => $otherCompany->id,
        'work_project_id' => $otherProject->id,
        'supplier_id' => $otherSupplier->id,
        'order_id' => PurchaseOrder::factory()->create([
            'company_id' => $otherCompany->id,
            'work_project_id' => $otherProject->id,
            'supplier_id' => $otherSupplier->id,
        ])->id,
        'contract_number' => 'CT-OTRA',
        'contract_type' => 'Servicio',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'total_amount' => 2500,
        'currency' => 'PEN',
        'status' => 'aprobado',
    ]);

    $otherSchedule = ContractPaymentSchedule::query()->create([
        'company_id' => $otherCompany->id,
        'supplier_contract_id' => $otherContract->id,
        'installment_number' => 1,
        'description' => 'Cuota externa',
        'due_date' => now()->addDays(5)->toDateString(),
        'scheduled_amount' => 1000,
        'paid_amount' => 0,
        'balance' => 1000,
        'status' => 'pendiente',
    ]);

    Livewire::test(ManageSupplierPayments::class)
        ->call('openCreateModal')
        ->set('supplier_contract_id', $otherContract->id)
        ->set('supplier_id', $otherSupplier->id)
        ->set('work_project_id', $otherProject->id)
        ->set('contract_payment_schedule_id', $otherSchedule->id)
        ->set('payment_date', now()->toDateString())
        ->set('amount', '500')
        ->set('currency', 'PEN')
        ->set('operation_type_id', $this->operationType->id)
        ->set('payment_method_id', $this->paymentMethod->id)
        ->set('bank_id', $this->bank->id)
        ->set('responsible_user_id', $this->user->id)
        ->set('concept', 'Pago cruzado')
        ->call('savePayment')
        ->assertHasErrors([
            'supplier_contract_id',
            'supplier_id',
            'work_project_id',
            'contract_payment_schedule_id',
        ]);
});

test('payments pages render for authorized users', function () {
    $this->get(route('modules.payments'))->assertOk()->assertSee('Pagos a proveedores');
    $this->get(route('payments.schedules', $this->contract))->assertOk()->assertSee('Cronograma de pagos');
});
