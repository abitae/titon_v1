<?php

namespace App\Livewire\Purchases;

use App\Actions\Contracts\CreateSupplierContractFromOrder;
use App\Actions\Orders\RecordOrderConformity as RecordOrderConformityAction;
use App\Actions\Purchases\ApprovePurchaseOrder;
use App\Actions\Purchases\CancelPurchaseOrder;
use App\Actions\Purchases\ObservePurchaseOrder;
use App\Concerns\InteractsWithToast;
use App\Concerns\ViewsPurchaseOrderPdf;
use App\Enums\ConformityResult;
use App\Enums\OrderStatus;
use App\Models\PurchaseOrder;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManagePurchaseOrders extends Component
{
    use InteractsWithToast, ViewsPurchaseOrderPdf;

    public string $title = 'Ordenes de compra';

    public string $search = '';

    public string $statusFilter = '';

    public ?PurchaseOrder $selectedOrder = null;

    public bool $showDetailModal = false;

    public string $detailModalTab = 'datos';

    public string $approval_notes = '';

    public string $observation = '';

    public string $cancellation_reason = '';

    public ?PurchaseOrder $conformityOrder = null;

    public bool $showConformityModal = false;

    public string $conformity_result = 'conforme';

    public string $conformity_observation = '';

    public string $conformity_date = '';

    public string $conformity_confirmation = '';

    public function render(): View
    {
        $orders = PurchaseOrder::query()
            ->with(['project', 'supplier', 'quotation', 'contract', 'accountsPayable', 'conformity', 'media'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('conditions', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.purchases.manage-purchase-orders', [
            'orders' => $orders,
            'statusOptions' => OrderStatus::cases(),
            'conformityResultOptions' => ConformityResult::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openDetailModal(int $purchaseOrderId): void
    {
        $this->selectedOrder = PurchaseOrder::query()
            ->with(['project', 'supplier', 'quotation', 'items', 'contract', 'approvedByUser', 'cancelledByUser'])
            ->findOrFail($purchaseOrderId);

        if ($this->selectedOrder->issue_date === null) {
            $this->selectedOrder->issue_date = DefaultDate::today();
        }

        $this->detailModalTab = 'datos';
        $this->showDetailModal = true;
    }

    public function approveOrder(ApprovePurchaseOrder $approvePurchaseOrder): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);
        abort_if($this->selectedOrder === null, 404);

        $approvePurchaseOrder->handle($this->selectedOrder, auth()->user(), $this->approval_notes ?: null);
        $this->selectedOrder->refresh();
        $this->approval_notes = '';
        $this->successToast('Orden de compra aprobada correctamente.');
    }

    public function observeOrder(ObservePurchaseOrder $observePurchaseOrder): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);
        abort_if($this->selectedOrder === null, 404);

        $validated = $this->validate([
            'observation' => ['required', 'string'],
        ]);

        $observePurchaseOrder->handle($this->selectedOrder, $validated['observation']);
        $this->selectedOrder->refresh();
        $this->observation = '';
        $this->warningToast('Orden de compra observada correctamente.');
    }

    public function cancelOrder(CancelPurchaseOrder $cancelPurchaseOrder): void
    {
        abort_unless(auth()->user()->can('purchases.aprobar'), 403);
        abort_if($this->selectedOrder === null, 404);

        $validated = $this->validate([
            'cancellation_reason' => ['required', 'string'],
        ]);

        $cancelPurchaseOrder->handle($this->selectedOrder, auth()->user(), $validated['cancellation_reason']);
        $this->selectedOrder->refresh();
        $this->cancellation_reason = '';
        $this->warningToast('Orden de compra anulada correctamente.');
    }

    public function createContract(CreateSupplierContractFromOrder $createSupplierContractFromOrder): void
    {
        abort_unless(auth()->user()->can('contracts.crear'), 403);
        abort_if($this->selectedOrder === null, 404);

        $createSupplierContractFromOrder->handle($this->selectedOrder);
        $this->selectedOrder->refresh();
        $this->successToast('Contrato generado correctamente desde la orden de compra.');
    }

    public function updateOrder(): void
    {
        abort_unless(auth()->user()->can('purchases.editar'), 403);
        abort_if($this->selectedOrder === null, 404);

        $validated = $this->validate([
            'selectedOrder.issue_date' => ['required', 'date'],
            'selectedOrder.currency' => ['required', 'string', 'max:10'],
            'selectedOrder.status' => ['required', Rule::in(OrderStatus::values())],
            'selectedOrder.conditions' => ['nullable', 'string'],
            'selectedOrder.observation' => ['nullable', 'string'],
        ]);

        $this->selectedOrder->update($validated['selectedOrder']);
        $this->selectedOrder->refresh();
        $this->successToast('Orden de compra actualizada correctamente.');
    }

    public function closeModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedOrder = null;
        $this->detailModalTab = 'datos';
        $this->approval_notes = '';
        $this->observation = '';
        $this->cancellation_reason = '';
    }

    public function openConformityModal(int $purchaseOrderId): void
    {
        abort_unless(
            auth()->user()->can('ordenes.conformidad')
            || auth()->user()->can('ordenes.rechazar')
            || auth()->user()->can('purchases.aprobar'),
            403,
        );

        $this->conformityOrder = PurchaseOrder::query()
            ->with(['project', 'supplier', 'conformity'])
            ->findOrFail($purchaseOrderId);

        $existingConformity = $this->conformityOrder->conformity;

        $this->conformity_result = $existingConformity?->result ?? ConformityResult::Conform->value();
        $this->conformity_observation = $existingConformity?->observation ?? '';
        $this->conformity_date = $existingConformity?->conformity_date?->format('Y-m-d') ?? DefaultDate::today();
        $this->conformity_confirmation = '';
        $this->showConformityModal = true;
    }

    public function saveConformity(RecordOrderConformityAction $recordOrderConformity): void
    {
        abort_unless(
            auth()->user()->can('ordenes.conformidad')
            || auth()->user()->can('ordenes.rechazar')
            || auth()->user()->can('purchases.aprobar'),
            403,
        );
        abort_if($this->conformityOrder === null, 404);

        $rules = [
            'conformity_result' => ['required', Rule::in([
                ConformityResult::Conform->value(),
                ConformityResult::Rejected->value(),
            ])],
            'conformity_date' => ['required', 'date'],
            'conformity_observation' => ['nullable', 'string'],
        ];

        if ($this->conformity_result === ConformityResult::Rejected->value()) {
            $rules['conformity_observation'] = ['required', 'string'];
        }

        if ($this->conformity_result === ConformityResult::Conform->value()) {
            $rules['conformity_confirmation'] = [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (mb_strtolower(trim((string) $value)) !== 'conforme') {
                        $fail('Debe escribir la palabra conforme para confirmar.');
                    }
                },
            ];
        }

        $validated = $this->validate($rules, [], [
            'conformity_result' => 'resultado',
            'conformity_date' => 'fecha',
            'conformity_observation' => 'observación',
            'conformity_confirmation' => 'confirmación',
        ]);

        $orderId = $this->conformityOrder->id;
        $isConform = $validated['conformity_result'] === ConformityResult::Conform->value();

        $recordOrderConformity->handle(
            $this->conformityOrder,
            auth()->user(),
            $validated['conformity_result'],
            $validated['conformity_observation'] ?: null,
            $validated['conformity_date'],
        );

        $this->closeConformityModal();

        if ($isConform) {
            $accountsPayable = PurchaseOrder::query()
                ->with('accountsPayable')
                ->find($orderId)
                ?->accountsPayable;

            if ($accountsPayable !== null) {
                $this->successToast('Conformidad registrada. La orden pasó a cuentas por pagar.');

                $this->redirect(route('accounts-payable.show', $accountsPayable), navigate: true);

                return;
            }
        }

        $this->successToast('Conformidad registrada correctamente.');
    }

    public function closeConformityModal(): void
    {
        $this->showConformityModal = false;
        $this->conformityOrder = null;
        $this->conformity_result = ConformityResult::Conform->value();
        $this->conformity_observation = '';
        $this->conformity_date = DefaultDate::today();
        $this->conformity_confirmation = '';
        $this->resetValidation([
            'conformity_result',
            'conformity_date',
            'conformity_observation',
            'conformity_confirmation',
        ]);
    }
}
