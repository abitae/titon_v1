<?php

namespace App\Livewire\Contracts;

use App\Actions\Contracts\ApproveSupplierContract;
use App\Actions\Contracts\CancelSupplierContract;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPdfInModal;
use App\Enums\SupplierContractStatus;
use App\Models\PurchaseOrder;
use App\Models\SupplierContract;
use App\Services\Audit\UserAuditLogger;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageSupplierContracts extends Component
{
    use InteractsWithToast, ViewsPdfInModal, WithFileUploads;

    public function mount(): void
    {
        $contractId = request()->integer('contract');

        if ($contractId > 0) {
            $this->openDetailModal($contractId);
        }
    }

    public string $title = 'Contratos con proveedores';

    public string $search = '';

    public string $statusFilter = '';

    public ?SupplierContract $selectedContract = null;

    public bool $showDetailModal = false;

    public string $approval_notes = '';

    public string $cancellation_reason = '';

    public array $signed_contract_files = [];

    public function render(): View
    {
        $contracts = SupplierContract::query()
            ->with(['project', 'supplier', 'purchaseOrder'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('contract_number', 'like', '%'.$this->search.'%')
                        ->orWhere('contract_type', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.contracts.manage-supplier-contracts', [
            'contracts' => $contracts,
            'statusOptions' => SupplierContractStatus::cases(),
            'orders' => PurchaseOrder::query()->with(['supplier', 'project'])->orderByDesc('issue_date')->limit(50)->get(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openDetailModal(int $supplierContractId): void
    {
        $this->selectedContract = SupplierContract::query()
            ->with(['project', 'supplier', 'purchaseOrder', 'media', 'approvedByUser', 'cancelledByUser'])
            ->findOrFail($supplierContractId);

        if ($this->selectedContract->start_date === null) {
            $this->selectedContract->start_date = DefaultDate::today();
        }

        if ($this->selectedContract->end_date === null) {
            $this->selectedContract->end_date = DefaultDate::monthsAhead();
        }

        $this->showDetailModal = true;
    }

    public function updateContract(): void
    {
        abort_unless(auth()->user()->can('contracts.editar'), 403);
        abort_if($this->selectedContract === null, 404);

        $validated = $this->validate([
            'selectedContract.contract_number' => ['required', 'string', 'max:50'],
            'selectedContract.contract_type' => ['required', 'string', 'max:100'],
            'selectedContract.start_date' => ['nullable', 'date'],
            'selectedContract.end_date' => ['nullable', 'date', 'after_or_equal:selectedContract.start_date'],
            'selectedContract.total_amount' => ['required', 'numeric', 'min:0'],
            'selectedContract.currency' => ['required', 'string', 'max:10'],
            'selectedContract.payment_conditions' => ['nullable', 'string'],
            'selectedContract.penalties' => ['nullable', 'string'],
            'selectedContract.guarantees' => ['nullable', 'string'],
            'selectedContract.status' => ['required', Rule::in(SupplierContractStatus::values())],
            'selectedContract.observation' => ['nullable', 'string'],
        ]);

        $this->selectedContract->update($validated['selectedContract']);
        $this->selectedContract->refresh();
        $this->successToast('Contrato actualizado correctamente.');
    }

    public function approveContract(ApproveSupplierContract $approveSupplierContract): void
    {
        abort_unless(auth()->user()->can('contracts.aprobar'), 403);
        abort_if($this->selectedContract === null, 404);

        $approveSupplierContract->handle($this->selectedContract, auth()->user(), $this->approval_notes ?: null);
        $this->selectedContract->refresh();
        $this->approval_notes = '';
        $this->successToast('Contrato aprobado correctamente.');
    }

    public function cancelContract(CancelSupplierContract $cancelSupplierContract): void
    {
        abort_unless(auth()->user()->can('contracts.aprobar'), 403);
        abort_if($this->selectedContract === null, 404);

        $validated = $this->validate([
            'cancellation_reason' => ['required', 'string'],
        ]);

        $cancelSupplierContract->handle($this->selectedContract, auth()->user(), $validated['cancellation_reason']);
        $this->selectedContract->refresh();
        $this->cancellation_reason = '';
        $this->warningToast('Contrato anulado correctamente.');
    }

    public function uploadSignedContract(UserAuditLogger $userAuditLogger): void
    {
        abort_unless(auth()->user()->can('contracts.editar'), 403);
        abort_if($this->selectedContract === null, 404);

        $this->validate([
            'signed_contract_files.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        foreach ($this->signed_contract_files as $uploadedFile) {
            $this->selectedContract
                ->addMedia($uploadedFile->getRealPath())
                ->usingFileName($uploadedFile->getClientOriginalName())
                ->usingName(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('signed_contract', 'public');
        }

        $userAuditLogger->log(
            action: 'archivo_subido',
            module: 'Contratos',
            auditable: $this->selectedContract,
            newValues: ['archivos' => collect($this->signed_contract_files)->map->getClientOriginalName()->all()],
            observation: 'Carga de contrato firmado.',
        );

        $this->signed_contract_files = [];
        $this->selectedContract->refresh();
        $this->successToast('Contrato firmado cargado correctamente.');
    }

    public function closeModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedContract = null;
        $this->approval_notes = '';
        $this->cancellation_reason = '';
        $this->signed_contract_files = [];
    }
}
