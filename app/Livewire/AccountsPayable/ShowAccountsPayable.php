<?php

namespace App\Livewire\AccountsPayable;

use App\Actions\AccountsPayable\RegisterAccountsPayablePayment;
use App\Concerns\InteractsWithToast;
use App\Enums\AccountsPayableStatus;
use App\Enums\CatalogType;
use App\Models\AccountsPayable;
use App\Models\CatalogItem;
use App\Models\PayableDocument;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ShowAccountsPayable extends Component
{
    use InteractsWithToast, WithFileUploads;

    public string $title = 'Detalle CxP';

    public AccountsPayable $accountsPayable;

    public string $payment_amount = '';

    public string $payment_date = '';

    public string $concept = '';

    public ?int $payment_method_id = null;

    public ?int $bank_id = null;

    public function mount(AccountsPayable $accountsPayable): void
    {
        $this->accountsPayable = $accountsPayable->load(['supplier', 'project', 'order', 'documents', 'payments']);
        $this->payment_date = now()->toDateString();
        $this->payment_amount = (string) $accountsPayable->balance;
        $this->concept = 'Pago '.$accountsPayable->code;
    }

    public function render(): View
    {
        $companyId = $this->accountsPayable->company_id;

        return view('livewire.accounts-payable.show-accounts-payable', [
            'catalogPaymentMethods' => CatalogItem::query()->where('company_id', $companyId)->where('type', CatalogType::PaymentMethod->value())->get(),
            'catalogBanks' => CatalogItem::query()->where('company_id', $companyId)->where('type', CatalogType::Bank->value())->get(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function markDocumentUploaded(int $documentId): void
    {
        abort_unless(auth()->user()->can('cuentas_pagar.subir_documentos') || auth()->user()->can('payments.crear'), 403);

        $document = PayableDocument::query()
            ->where('accounts_payable_id', $this->accountsPayable->id)
            ->findOrFail($documentId);

        $document->update([
            'uploaded' => true,
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
            'status' => 'cargado',
        ]);

        $this->accountsPayable->refresh();

        if ($this->accountsPayable->requiredDocumentsUploaded()) {
            $this->accountsPayable->update(['status' => AccountsPayableStatus::ReadyForPayment->value()]);
        }

        $this->successToast('Documento marcado como cargado.');
    }

    public function registerPayment(RegisterAccountsPayablePayment $registerPayment): void
    {
        abort_unless(auth()->user()->can('cuentas_pagar.pagar') || auth()->user()->can('payments.crear'), 403);

        $validated = $this->validate([
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'concept' => ['required', 'string', 'max:255'],
            'payment_method_id' => ['nullable', 'integer'],
            'bank_id' => ['nullable', 'integer'],
        ]);

        $registerPayment->handle($this->accountsPayable, [
            'amount' => $validated['payment_amount'],
            'payment_date' => $validated['payment_date'],
            'concept' => $validated['concept'],
            'payment_method_id' => $validated['payment_method_id'],
            'bank_id' => $validated['bank_id'],
            'currency' => $this->accountsPayable->currency,
        ], auth()->user());

        $this->accountsPayable->refresh();
        $this->payment_amount = (string) $this->accountsPayable->balance;
        $this->successToast('Pago registrado.');
    }
}
