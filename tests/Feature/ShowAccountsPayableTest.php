<?php

use App\Actions\AccountsPayable\InitializePayableDocuments;
use App\Enums\AccountsPayableStatus;
use App\Enums\CatalogType;
use App\Livewire\AccountsPayable\ShowAccountsPayable;
use App\Models\AccountsPayable;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\Order;
use App\Models\PayableDocument;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Companies\CompanyContext;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');

    $this->seed(PermissionSeeder::class);

    $this->company = Company::factory()->create();
    $this->seed(CatalogSeeder::class);
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

    $this->project = Project::factory()->create(['company_id' => $this->company->id]);
    $this->supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
    $this->order = Order::factory()->create([
        'company_id' => $this->company->id,
        'work_project_id' => $this->project->id,
        'supplier_id' => $this->supplier->id,
        'total' => 1500,
    ]);

    $this->accountsPayable = AccountsPayable::factory()->create([
        'company_id' => $this->company->id,
        'order_id' => $this->order->id,
        'supplier_id' => $this->supplier->id,
        'work_project_id' => $this->project->id,
        'amount' => 1500,
        'balance' => 1500,
    ]);

    app(InitializePayableDocuments::class)->handle($this->accountsPayable);

    $this->bankInstitution = CatalogItem::query()
        ->where('company_id', $this->company->id)
        ->where('type', CatalogType::Bank->value())
        ->where('code', 'BCP')
        ->firstOrFail();

    $this->bankAccount = BankAccount::factory()->create([
        'company_id' => $this->company->id,
        'catalog_bank_id' => $this->bankInstitution->id,
        'balance' => 5000,
        'is_cash' => false,
    ]);

    $this->cashAccount = BankAccount::factory()->cash()->create([
        'company_id' => $this->company->id,
        'balance' => 3000,
    ]);

    $this->cashPaymentMethod = CatalogItem::query()
        ->where('company_id', $this->company->id)
        ->where('type', CatalogType::PaymentMethod->value())
        ->where('code', 'EFE')
        ->firstOrFail();

    $this->depositPaymentMethod = CatalogItem::query()
        ->where('company_id', $this->company->id)
        ->where('type', CatalogType::PaymentMethod->value())
        ->where('code', 'DEP')
        ->firstOrFail();

    $this->operationType = CatalogItem::query()
        ->where('company_id', $this->company->id)
        ->where('type', CatalogType::OperationType->value())
        ->firstOrFail();
});

test('payable checklist documents can be uploaded to the server', function () {
    $document = PayableDocument::query()
        ->where('accounts_payable_id', $this->accountsPayable->id)
        ->where('document_type', 'factura')
        ->firstOrFail();

    Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $this->accountsPayable])
        ->set('document_files.'.$document->id, UploadedFile::fake()->create('factura.pdf', 120, 'application/pdf'))
        ->call('uploadDocument', $document->id)
        ->assertHasNoErrors();

    $document->refresh();

    expect($document->hasUploadedFile())->toBeTrue();
    expect($document->uploaded)->toBeTrue();
});

test('required documents are not considered loaded without an uploaded file', function () {
    PayableDocument::query()
        ->where('accounts_payable_id', $this->accountsPayable->id)
        ->where('required', true)
        ->get()
        ->each(function (PayableDocument $document): void {
            $document->update([
                'uploaded' => true,
                'uploaded_by' => $this->user->id,
                'uploaded_at' => now(),
                'status' => 'cargado',
            ]);
        });

    expect($this->accountsPayable->fresh()->requiredDocumentsUploaded())->toBeFalse();
});

test('payment registration modal can be opened and closed', function () {
    uploadRequiredDocuments($this->accountsPayable);

    Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $this->accountsPayable->fresh()])
        ->assertSet('showPaymentModal', false)
        ->call('openPaymentModal')
        ->assertSet('showPaymentModal', true)
        ->assertSet('payment_amount', '1500')
        ->call('closePaymentModal')
        ->assertSet('showPaymentModal', false);
});

test('banking payment method requires account operation type and operation number', function () {
    uploadRequiredDocuments($this->accountsPayable);

    Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $this->accountsPayable->fresh()])
        ->set('payment_method_id', $this->depositPaymentMethod->id)
        ->set('payment_amount', '1500')
        ->set('payment_date', now()->toDateString())
        ->call('registerPayment')
        ->assertHasErrors(['bank_account_id', 'operation_type_id', 'operation_number']);
});

test('cash payment can be registered after required documents are uploaded', function () {
    uploadRequiredDocuments($this->accountsPayable);

    Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $this->accountsPayable->fresh()])
        ->call('openPaymentModal')
        ->assertSet('showPaymentModal', true)
        ->set('payment_method_id', $this->cashPaymentMethod->id)
        ->set('bank_account_id', $this->cashAccount->id)
        ->set('payment_amount', '1500')
        ->set('payment_date', now()->toDateString())
        ->call('registerPayment')
        ->assertHasNoErrors()
        ->assertSet('showPaymentModal', false);

    $this->accountsPayable->refresh();
    $this->cashAccount->refresh();

    expect((float) $this->accountsPayable->balance)->toBe(0.0);
    expect($this->accountsPayable->status)->toBe(AccountsPayableStatus::Paid->value());
    expect($this->accountsPayable->payments()->first()?->operation_number)->toBeNull();
    expect($this->accountsPayable->payments()->first()?->payment_method_id)->toBe($this->cashPaymentMethod->id);
    expect((float) $this->cashAccount->balance)->toBe(1500.0);
});

test('payment rejects bank account with different currency', function () {
    uploadRequiredDocuments($this->accountsPayable);

    $usdAccount = BankAccount::factory()->create([
        'company_id' => $this->company->id,
        'currency' => 'USD',
        'balance' => 10000,
        'is_cash' => false,
    ]);

    Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $this->accountsPayable->fresh()])
        ->set('payment_method_id', $this->depositPaymentMethod->id)
        ->set('bank_account_id', $usdAccount->id)
        ->set('payment_amount', '1500')
        ->set('payment_date', now()->toDateString())
        ->set('operation_type_id', $this->operationType->id)
        ->set('operation_number', 'OP-USD-1')
        ->call('registerPayment')
        ->assertHasErrors(['bank_account_id']);
});

test('banking payment stores account operation type and operation number', function () {
    uploadRequiredDocuments($this->accountsPayable);

    Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $this->accountsPayable->fresh()])
        ->set('payment_method_id', $this->depositPaymentMethod->id)
        ->set('bank_account_id', $this->bankAccount->id)
        ->set('payment_amount', '1500')
        ->set('payment_date', now()->toDateString())
        ->set('operation_type_id', $this->operationType->id)
        ->set('operation_number', 'OP-778899')
        ->call('registerPayment')
        ->assertHasNoErrors();

    $payment = $this->accountsPayable->fresh()->payments()->first();
    $this->bankAccount->refresh();

    expect($payment)->not->toBeNull();
    expect($payment?->payment_method_id)->toBe($this->depositPaymentMethod->id);
    expect($payment?->bank_account_id)->toBe($this->bankAccount->id);
    expect($payment?->operation_type_id)->toBe($this->operationType->id);
    expect($payment?->operation_number)->toBe('OP-778899');
    expect((float) $this->bankAccount->balance)->toBe(3500.0);
    expect($this->bankAccount->movements()->count())->toBe(1);
});

function uploadRequiredDocuments(AccountsPayable $accountsPayable): void
{
    $component = Livewire::test(ShowAccountsPayable::class, ['accountsPayable' => $accountsPayable]);

    PayableDocument::query()
        ->where('accounts_payable_id', $accountsPayable->id)
        ->where('required', true)
        ->get()
        ->each(function (PayableDocument $document) use ($component): void {
            $component
                ->set('document_files.'.$document->id, UploadedFile::fake()->create($document->document_type.'.pdf', 100, 'application/pdf'))
                ->call('uploadDocument', $document->id)
                ->assertHasNoErrors();
        });
}
