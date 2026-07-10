<?php

namespace App\Livewire\Payments;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Payments\RegisterSupplierPayment;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\ContractPaymentSchedule;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\SupplierPayment;
use App\Services\Audit\UserAuditLogger;
use App\Services\Payments\SupplierAccountSummary;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageSupplierPayments extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Pagos a proveedores';

    public string $search = '';

    public string $projectFilter = '';

    public string $supplierFilter = '';

    public string $contractFilter = '';

    public bool $showFormModal = false;

    public ?int $work_project_id = null;

    public ?int $supplier_contract_id = null;

    public ?int $supplier_id = null;

    public ?int $contract_payment_schedule_id = null;

    public string $payment_date = '';

    public string $amount = '0';

    public string $currency = 'PEN';

    public ?int $operation_type_id = null;

    public ?int $payment_method_id = null;

    public ?int $bank_account_id = null;

    public string $operation_number = '';

    public ?int $responsible_user_id = null;

    public string $concept = '';

    public string $observation = '';

    public array $voucher = [];

    public function mount(): void
    {
        $this->payment_date = DefaultDate::today();
        $this->responsible_user_id = auth()->id();
    }

    public function render(SupplierAccountSummary $supplierAccountSummary): View
    {
        $contractsWithTotals = SupplierContract::query()
            ->with(['supplier', 'project'])
            ->withSum('payments', 'amount')
            ->orderByDesc('created_at')
            ->get();

        $payments = SupplierPayment::query()
            ->with(['project', 'supplier', 'supplierContract', 'schedule', 'operationType', 'paymentMethod', 'bank'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('operation_number', 'like', '%'.$this->search.'%')
                        ->orWhere('concept', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->projectFilter !== '', fn ($query) => $query->where('work_project_id', $this->projectFilter))
            ->when($this->supplierFilter !== '', fn ($query) => $query->where('supplier_id', $this->supplierFilter))
            ->when($this->contractFilter !== '', fn ($query) => $query->where('supplier_contract_id', $this->contractFilter))
            ->latest('payment_date')
            ->paginate(12);

        $supplierSummaries = $this->supplierFilter !== ''
            ? $supplierAccountSummary->bySupplier((int) $this->supplierFilter)
            : collect();

        return view('livewire.payments.manage-supplier-payments', [
            'payments' => $payments,
            'projects' => Project::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('business_name')->get(),
            'contracts' => $contractsWithTotals,
            'schedules' => $this->availableSchedules(),
            'companyUsers' => $this->companyUsers(),
            'bankAccounts' => BankAccount::query()->with('institution')->where('is_active', true)->orderBy('is_cash')->orderBy('name')->get(),
            'paymentMethods' => CatalogItem::query()->ofType(CatalogType::PaymentMethod)->where('is_active', true)->orderBy('name')->get(),
            'operationTypes' => CatalogItem::query()->ofType(CatalogType::OperationType)->where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'total_paid' => (float) SupplierPayment::query()->sum('amount'),
                'contracts_with_balance' => $contractsWithTotals
                    ->filter(fn (SupplierContract $contract): bool => (float) $contract->total_amount > (float) ($contract->payments_sum_amount ?? 0))
                    ->count(),
                'payments_count' => SupplierPayment::query()->count(),
            ],
            'supplierSummaries' => $supplierSummaries,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSupplierContractId(): void
    {
        $contract = $this->selectedContract();

        $this->supplier_id = $contract?->supplier_id;
        $this->work_project_id = $contract?->work_project_id;
        $this->currency = $contract?->currency ?? 'PEN';
        $this->contract_payment_schedule_id = null;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function savePayment(RegisterSupplierPayment $registerSupplierPayment, UserAuditLogger $userAuditLogger): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());
        $activeCompanyUserIds = $company?->users()
            ->wherePivot('active', true)
            ->pluck('users.id')
            ->all() ?? [];

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can('payments.crear'), 403);

        $validated = $this->validate([
            'work_project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where(fn ($query) => $query->where('company_id', $company->id))],
            'supplier_contract_id' => ['required', 'integer', Rule::exists('supplier_contracts', 'id')->where(fn ($query) => $query->where('company_id', $company->id))],
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where(fn ($query) => $query->where('company_id', $company->id))],
            'contract_payment_schedule_id' => ['nullable', 'integer', Rule::exists('contract_payment_schedules', 'id')->where(fn ($query) => $query->where('company_id', $company->id))],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'max:10'],
            'operation_type_id' => ['nullable', 'integer', Rule::exists('catalog_items', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('type', CatalogType::OperationType->value()))],
            'payment_method_id' => ['nullable', 'integer', Rule::exists('catalog_items', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('type', CatalogType::PaymentMethod->value()))],
            'bank_account_id' => ['required', 'integer', Rule::exists('bank_accounts', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_active', true))],
            'operation_number' => ['nullable', 'string', 'max:100'],
            'responsible_user_id' => ['required', 'integer', Rule::in($activeCompanyUserIds)],
            'concept' => ['required', 'string', 'max:255'],
            'observation' => ['nullable', 'string'],
            'voucher.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $contract = SupplierContract::query()
            ->withSum('payments', 'amount')
            ->findOrFail($validated['supplier_contract_id']);
        $schedule = $validated['contract_payment_schedule_id']
            ? ContractPaymentSchedule::query()
                ->whereKey($validated['contract_payment_schedule_id'])
                ->where('supplier_contract_id', $contract->id)
                ->first()
            : null;

        $validator = validator([], []);

        $validator->after(function (Validator $validator) use ($contract, $schedule, $validated): void {
            $amount = (float) $validated['amount'];

            if ((int) $contract->supplier_id !== (int) $validated['supplier_id']) {
                $validator->errors()->add('supplier_id', 'El proveedor seleccionado no corresponde al contrato.');
            }

            if ((int) $contract->work_project_id !== (int) $validated['work_project_id']) {
                $validator->errors()->add('work_project_id', 'La obra seleccionada no corresponde al contrato.');
            }

            if ($validated['contract_payment_schedule_id'] !== null && $schedule === null) {
                $validator->errors()->add('contract_payment_schedule_id', 'La cuota seleccionada no corresponde al contrato.');
            }

            if ($amount > $contract->pendingBalance()) {
                $validator->errors()->add('amount', 'El pago no puede ser mayor al saldo pendiente del contrato.');
            }

            if ($schedule !== null && $amount > (float) $schedule->balance) {
                $validator->errors()->add('amount', 'El pago no puede ser mayor al saldo pendiente de la cuota seleccionada.');
            }
        });

        $validator->validate();

        $payment = $registerSupplierPayment->handle([
            ...$validated,
            'company_id' => $company->id,
        ], auth()->user());

        foreach ($this->voucher as $uploadedFile) {
            $payment
                ->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->usingName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('voucher', 'public');
        }

        if ($this->voucher !== []) {
            $userAuditLogger->log(
                action: 'archivo_subido',
                module: 'Pagos a proveedores',
                auditable: $payment,
                newValues: ['archivos' => collect($this->voucher)->map->getClientOriginalName()->all()],
                observation: 'Carga de voucher de pago.',
            );
        }

        $this->resetForm();
        $this->successToast('Pago registrado correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'work_project_id',
            'supplier_contract_id',
            'supplier_id',
            'contract_payment_schedule_id',
            'amount',
            'operation_type_id',
            'payment_method_id',
            'bank_account_id',
            'operation_number',
            'concept',
            'observation',
            'voucher',
        ]);

        $this->payment_date = DefaultDate::today();
        $this->responsible_user_id = auth()->id();
        $this->currency = 'PEN';
        $this->showFormModal = false;
    }

    protected function availableSchedules()
    {
        if ($this->supplier_contract_id === null) {
            return collect();
        }

        return ContractPaymentSchedule::query()
            ->where('supplier_contract_id', $this->supplier_contract_id)
            ->orderBy('installment_number')
            ->get();
    }

    protected function selectedContract(): ?SupplierContract
    {
        return $this->supplier_contract_id
            ? SupplierContract::query()->find($this->supplier_contract_id)
            : null;
    }

    protected function companyUsers()
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        return $company?->users()->wherePivot('active', true)->orderBy('name')->get() ?? collect();
    }
}
