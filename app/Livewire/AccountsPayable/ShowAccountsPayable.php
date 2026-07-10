<?php

namespace App\Livewire\AccountsPayable;

use App\Actions\AccountsPayable\RegisterAccountsPayablePayment;
use App\Actions\AccountsPayable\UploadPayableDocument;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPdfInModal;
use App\Enums\CatalogType;
use App\Models\AccountsPayable;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\PayableDocument;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ShowAccountsPayable extends Component
{
    use InteractsWithToast, ViewsPdfInModal, WithFileUploads;

    public function openPayableDocumentPreview(int $documentId): void
    {
        $document = PayableDocument::query()
            ->where('accounts_payable_id', $this->accountsPayable->id)
            ->findOrFail($documentId);

        $media = $document->getFirstMedia('archivo');

        abort_if($media === null, 404);

        $this->openPdfModal($media->getUrl(), $document->typeLabel(), 'Documento de cuenta por pagar');
    }

    public string $title = 'Detalle CxP';

    public AccountsPayable $accountsPayable;

    public string $payment_amount = '';

    public string $payment_date = '';

    public string $concept = '';

    public ?int $payment_method_id = null;

    public ?int $bank_account_id = null;

    public ?int $operation_type_id = null;

    public string $operation_number = '';

    public bool $showPaymentModal = false;

    /**
     * @var array<int, TemporaryUploadedFile|null>
     */
    public array $document_files = [];

    public function mount(AccountsPayable $accountsPayable): void
    {
        $this->accountsPayable = $this->loadAccountsPayableRelations($accountsPayable);
        $this->payment_date = DefaultDate::today();
        $this->payment_amount = (string) $accountsPayable->balance;
        $this->concept = 'Pago '.$accountsPayable->code;
    }

    public function render(): View
    {
        $companyId = $this->accountsPayable->company_id;

        return view('livewire.accounts-payable.show-accounts-payable', [
            'paymentMethods' => CatalogItem::query()
                ->where('company_id', $companyId)
                ->ofType(CatalogType::PaymentMethod)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'bankAccounts' => $this->payment_method_id !== null
                ? BankAccount::query()
                    ->with('institution')
                    ->where('company_id', $companyId)
                    ->availableForPayment(
                        $this->accountsPayable->currency,
                        $this->paymentMethodRequiresBankingDetails(),
                    )
                    ->get()
                : collect(),
            'configuredCashAccounts' => BankAccount::query()
                ->where('company_id', $companyId)
                ->availableForPayment($this->accountsPayable->currency, false)
                ->count(),
            'configuredBankAccounts' => BankAccount::query()
                ->where('company_id', $companyId)
                ->availableForPayment($this->accountsPayable->currency, true)
                ->count(),
            'operationTypes' => CatalogItem::query()
                ->where('company_id', $companyId)
                ->ofType(CatalogType::OperationType)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedPaymentMethodId(): void
    {
        $this->bank_account_id = null;

        if (! $this->paymentMethodRequiresBankingDetails()) {
            $this->operation_type_id = null;
            $this->operation_number = '';
        }
    }

    public function paymentMethodRequiresBankingDetails(): bool
    {
        $paymentMethod = $this->selectedPaymentMethod();

        return $paymentMethod?->requiresBankingDetails() ?? false;
    }

    public function uploadDocument(int $documentId, UploadPayableDocument $uploadPayableDocument): void
    {
        abort_unless(
            auth()->user()->can('cuentas_pagar.subir_documentos') || auth()->user()->can('payments.crear'),
            403,
        );

        $this->validate([
            "document_files.$documentId" => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [], [
            "document_files.$documentId" => 'archivo',
        ]);

        $document = PayableDocument::query()
            ->where('accounts_payable_id', $this->accountsPayable->id)
            ->findOrFail($documentId);

        $uploadPayableDocument->handle(
            $this->accountsPayable,
            $document,
            $this->document_files[$documentId],
            auth()->user(),
        );

        unset($this->document_files[$documentId]);

        $this->accountsPayable->load(['documents.media']);
        $this->accountsPayable->refresh();

        $this->successToast('Documento subido correctamente.');
    }

    public function openPaymentModal(): void
    {
        abort_unless(auth()->user()->can('cuentas_pagar.pagar') || auth()->user()->can('payments.crear'), 403);

        $this->resetPaymentForm();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    public function registerPayment(RegisterAccountsPayablePayment $registerPayment): void
    {
        abort_unless(auth()->user()->can('cuentas_pagar.pagar') || auth()->user()->can('payments.crear'), 403);

        $companyId = $this->accountsPayable->company_id;

        $rules = [
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'concept' => ['required', 'string', 'max:255'],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('catalog_items', 'id')->where(
                    fn ($query) => $query
                        ->where('company_id', $companyId)
                        ->where('type', CatalogType::PaymentMethod->value())
                        ->where('is_active', true),
                ),
            ],
            'bank_account_id' => [
                'required',
                'integer',
                Rule::exists('bank_accounts', 'id')->where(
                    fn ($query) => $query
                        ->where('company_id', $companyId)
                        ->where('is_active', true)
                        ->where('currency', $this->accountsPayable->currency),
                ),
            ],
        ];

        if ($this->paymentMethodRequiresBankingDetails()) {
            $rules['operation_type_id'] = [
                'required',
                'integer',
                Rule::exists('catalog_items', 'id')->where(
                    fn ($query) => $query->where('company_id', $companyId)->where('type', CatalogType::OperationType->value()),
                ),
            ];
            $rules['operation_number'] = ['required', 'string', 'max:100'];
        }

        $validated = $this->validate($rules, [], [
            'payment_amount' => 'monto',
            'payment_date' => 'fecha de pago',
            'concept' => 'concepto',
            'payment_method_id' => 'método de pago',
            'bank_account_id' => 'cuenta',
            'operation_type_id' => 'tipo de operación',
            'operation_number' => 'número de operación',
        ]);

        $requiresBankingDetails = $this->paymentMethodRequiresBankingDetails();

        $registerPayment->handle($this->accountsPayable, [
            'amount' => $validated['payment_amount'],
            'payment_date' => $validated['payment_date'],
            'concept' => $validated['concept'],
            'payment_method_id' => $validated['payment_method_id'],
            'bank_account_id' => $validated['bank_account_id'],
            'operation_type_id' => $requiresBankingDetails ? $validated['operation_type_id'] : null,
            'operation_number' => $requiresBankingDetails ? $validated['operation_number'] : null,
            'currency' => $this->accountsPayable->currency,
        ], auth()->user());

        $this->accountsPayable = $this->loadAccountsPayableRelations($this->accountsPayable->fresh());
        $this->closePaymentModal();

        $this->successToast('Pago registrado.');
    }

    public function canRegisterPayment(): bool
    {
        if (! $this->accountsPayable->requiredDocumentsUploaded() || (float) $this->accountsPayable->balance <= 0) {
            return false;
        }

        return BankAccount::query()
            ->where('company_id', $this->accountsPayable->company_id)
            ->where('is_active', true)
            ->where('currency', $this->accountsPayable->currency)
            ->exists();
    }

    protected function selectedPaymentMethod(): ?CatalogItem
    {
        if ($this->payment_method_id === null) {
            return null;
        }

        return CatalogItem::query()
            ->whereKey($this->payment_method_id)
            ->where('company_id', $this->accountsPayable->company_id)
            ->ofType(CatalogType::PaymentMethod)
            ->first();
    }

    protected function loadAccountsPayableRelations(AccountsPayable $accountsPayable): AccountsPayable
    {
        return $accountsPayable->load([
            'supplier',
            'project',
            'order',
            'documents.media',
            'payments.bankAccount.institution',
            'payments.paymentMethod',
            'payments.operationType',
            'payments.payer',
            'payments.bankMovement',
        ]);
    }

    protected function resetPaymentForm(): void
    {
        $this->resetErrorBag();
        $this->payment_date = DefaultDate::today();
        $this->payment_amount = (string) $this->accountsPayable->balance;
        $this->concept = 'Pago '.$this->accountsPayable->code;
        $this->payment_method_id = null;
        $this->bank_account_id = null;
        $this->operation_type_id = null;
        $this->operation_number = '';
    }
}
