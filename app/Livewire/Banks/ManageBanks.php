<?php

namespace App\Livewire\Banks;

use App\Actions\Banks\RecordBankMovement;
use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\BankMovementType;
use App\Enums\CatalogType;
use App\Models\BankAccount;
use App\Models\BankMovement;
use App\Models\CatalogItem;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageBanks extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Bancos y caja';

    public string $accountSearch = '';

    public string $movementSearch = '';

    public ?int $accountFilter = null;

    public string $movementTypeFilter = 'all';

    public bool $showAccountModal = false;

    public bool $showMovementModal = false;

    public ?int $editingAccountId = null;

    public string $account_name = '';

    public ?int $catalog_bank_id = null;

    public string $account_number = '';

    public string $currency = 'PEN';

    public string $opening_balance = '0';

    public bool $is_cash = false;

    public bool $is_active = true;

    public string $movement_kind = 'deposit';

    public ?int $movement_bank_account_id = null;

    public string $movement_amount = '';

    public string $movement_date = '';

    public string $movement_concept = '';

    public string $movement_reference = '';

    public function mount(): void
    {
        $this->movement_date = now()->toDateString();
    }

    public function render(): View
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());
        $companyId = $company?->id;

        $accounts = BankAccount::query()
            ->with('institution')
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($this->accountSearch !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('name', 'like', '%'.$this->accountSearch.'%')
                        ->orWhere('account_number', 'like', '%'.$this->accountSearch.'%');
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('is_cash')
            ->orderBy('name')
            ->paginate(10, pageName: 'accountsPage');

        $movements = BankMovement::query()
            ->with(['bankAccount.institution', 'createdBy'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($this->accountFilter, fn ($query) => $query->where('bank_account_id', $this->accountFilter))
            ->when($this->movementTypeFilter !== 'all', fn ($query) => $query->where('type', $this->movementTypeFilter))
            ->when($this->movementSearch !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('movement_code', 'like', '%'.$this->movementSearch.'%')
                        ->orWhere('concept', 'like', '%'.$this->movementSearch.'%')
                        ->orWhere('reference', 'like', '%'.$this->movementSearch.'%')
                        ->orWhere('operation_number', 'like', '%'.$this->movementSearch.'%');
                });
            })
            ->latest('movement_date')
            ->latest('id')
            ->paginate(12, pageName: 'movementsPage');

        $institutions = $companyId
            ? CatalogItem::query()
                ->where('company_id', $companyId)
                ->ofType(CatalogType::Bank)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : collect();

        $totalBalance = $companyId
            ? (float) BankAccount::query()->where('company_id', $companyId)->where('is_active', true)->sum('balance')
            : 0.0;

        return view('livewire.banks.manage-banks', [
            'accounts' => $accounts,
            'movements' => $movements,
            'institutions' => $institutions,
            'totalBalance' => $totalBalance,
            'movementTypes' => [
                BankMovementType::Deposit,
                BankMovementType::Withdrawal,
                BankMovementType::AccountsPayablePayment,
                BankMovementType::SupplierPayment,
            ],
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedAccountSearch(): void
    {
        $this->resetPage('accountsPage');
    }

    public function updatedMovementSearch(): void
    {
        $this->resetPage('movementsPage');
    }

    public function updatedAccountFilter(): void
    {
        $this->resetPage('movementsPage');
    }

    public function updatedMovementTypeFilter(): void
    {
        $this->resetPage('movementsPage');
    }

    public function openCreateAccountModal(): void
    {
        abort_unless(auth()->user()->can('bancos.crear'), 403);
        $this->resetAccountForm();
        $this->showAccountModal = true;
    }

    public function openEditAccountModal(int $accountId): void
    {
        abort_unless(auth()->user()->can('bancos.editar'), 403);

        $account = BankAccount::query()->findOrFail($accountId);

        $this->editingAccountId = $account->id;
        $this->account_name = $account->name;
        $this->catalog_bank_id = $account->catalog_bank_id;
        $this->account_number = $account->account_number ?? '';
        $this->currency = $account->currency;
        $this->is_cash = $account->is_cash;
        $this->is_active = $account->is_active;
        $this->opening_balance = '0';
        $this->showAccountModal = true;
    }

    public function saveAccount(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingAccountId ? 'bancos.editar' : 'bancos.crear'), 403);

        $validated = $this->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'catalog_bank_id' => [
                Rule::requiredIf(! $this->is_cash),
                'nullable',
                'integer',
                Rule::exists('catalog_items', 'id')->where(
                    fn ($query) => $query->where('company_id', $company->id)->where('type', CatalogType::Bank->value()),
                ),
            ],
            'account_number' => [
                Rule::requiredIf(! $this->is_cash),
                'nullable',
                'string',
                'max:50',
            ],
            'currency' => ['required', 'string', 'max:10'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'is_cash' => ['boolean'],
            'is_active' => ['boolean'],
        ], [], [
            'account_name' => 'nombre',
            'catalog_bank_id' => 'banco',
            'account_number' => 'número de cuenta',
            'opening_balance' => 'saldo inicial',
        ]);

        if ($this->editingAccountId) {
            $account = BankAccount::query()->findOrFail($this->editingAccountId);
            $account->update([
                'name' => $validated['account_name'],
                'catalog_bank_id' => $this->is_cash ? null : $validated['catalog_bank_id'],
                'account_number' => $this->is_cash ? null : $validated['account_number'],
                'currency' => $validated['currency'],
                'is_cash' => $this->is_cash,
                'is_active' => $this->is_active,
            ]);
        } else {
            $openingBalance = round((float) ($validated['opening_balance'] ?? 0), 2);

            $account = BankAccount::query()->create([
                'company_id' => $company->id,
                'name' => $validated['account_name'],
                'catalog_bank_id' => $this->is_cash ? null : $validated['catalog_bank_id'],
                'account_number' => $this->is_cash ? null : $validated['account_number'],
                'currency' => $validated['currency'],
                'balance' => 0,
                'is_cash' => $this->is_cash,
                'is_active' => $this->is_active,
            ]);

            if ($openingBalance > 0) {
                app(RecordBankMovement::class)->handle($account, auth()->user(), [
                    'type' => BankMovementType::Deposit->value(),
                    'amount' => $openingBalance,
                    'movement_date' => now()->toDateString(),
                    'concept' => 'Saldo inicial',
                    'reference' => 'Apertura de cuenta',
                ]);
            }
        }

        $this->showAccountModal = false;
        $this->resetAccountForm();
        $this->successToast('Cuenta guardada correctamente.');
    }

    public function openMovementModal(?int $accountId = null, string $kind = 'deposit'): void
    {
        abort_unless(auth()->user()->can('bancos.crear'), 403);

        $this->movement_kind = $kind;
        $this->movement_bank_account_id = $accountId;
        $this->movement_amount = '';
        $this->movement_date = now()->toDateString();
        $this->movement_concept = $kind === 'deposit' ? 'Depósito' : 'Retiro';
        $this->movement_reference = '';
        $this->showMovementModal = true;
    }

    public function saveMovement(RecordBankMovement $recordBankMovement): void
    {
        abort_unless(auth()->user()->can('bancos.crear'), 403);

        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());
        abort_if($company === null, 403);

        $validated = $this->validate([
            'movement_kind' => ['required', Rule::in(['deposit', 'withdrawal'])],
            'movement_bank_account_id' => [
                'required',
                'integer',
                Rule::exists('bank_accounts', 'id')->where(fn ($query) => $query->where('company_id', $company->id)->where('is_active', true)),
            ],
            'movement_amount' => ['required', 'numeric', 'min:0.01'],
            'movement_date' => ['required', 'date'],
            'movement_concept' => ['required', 'string', 'max:255'],
            'movement_reference' => ['nullable', 'string', 'max:255'],
        ], [], [
            'movement_bank_account_id' => 'cuenta',
            'movement_amount' => 'monto',
            'movement_date' => 'fecha',
            'movement_concept' => 'concepto',
        ]);

        $account = BankAccount::query()->findOrFail($validated['movement_bank_account_id']);

        if (
            $validated['movement_kind'] === 'withdrawal'
            && (float) $account->balance < (float) $validated['movement_amount']
        ) {
            $this->addError('movement_amount', 'Saldo insuficiente en la cuenta seleccionada.');

            return;
        }

        $type = $validated['movement_kind'] === 'deposit'
            ? BankMovementType::Deposit->value()
            : BankMovementType::Withdrawal->value();

        try {
            $recordBankMovement->handle($account, auth()->user(), [
                'type' => $type,
                'amount' => $validated['movement_amount'],
                'movement_date' => $validated['movement_date'],
                'concept' => $validated['movement_concept'],
                'reference' => $validated['movement_reference'] ?: null,
            ]);

            $this->showMovementModal = false;
            $this->successToast('Movimiento registrado.');
        } catch (\Throwable $exception) {
            $this->dangerToast($exception->getMessage());
        }
    }

    public function closeAccountModal(): void
    {
        $this->showAccountModal = false;
    }

    public function closeMovementModal(): void
    {
        $this->showMovementModal = false;
    }

    protected function resetAccountForm(): void
    {
        $this->reset([
            'editingAccountId',
            'account_name',
            'catalog_bank_id',
            'account_number',
            'opening_balance',
        ]);

        $this->currency = 'PEN';
        $this->is_cash = false;
        $this->is_active = true;
        $this->opening_balance = '0';
    }
}
