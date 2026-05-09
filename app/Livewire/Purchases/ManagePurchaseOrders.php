<?php

namespace App\Livewire\Purchases;

use App\Actions\Contracts\CreateSupplierContractFromOrder;
use App\Actions\Purchases\ApprovePurchaseOrder;
use App\Actions\Purchases\CancelPurchaseOrder;
use App\Actions\Purchases\ObservePurchaseOrder;
use App\Concerns\InteractsWithToast;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManagePurchaseOrders extends Component
{
    use InteractsWithToast;

    public string $title = 'Ordenes de compra';

    public string $search = '';

    public string $statusFilter = '';

    public ?PurchaseOrder $selectedOrder = null;

    public bool $showDetailModal = false;

    public string $approval_notes = '';

    public string $observation = '';

    public string $cancellation_reason = '';

    public function render(): View
    {
        $orders = PurchaseOrder::query()
            ->with(['project', 'supplier', 'quotation', 'contract'])
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
            'statusOptions' => PurchaseOrderStatus::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openDetailModal(int $purchaseOrderId): void
    {
        $this->selectedOrder = PurchaseOrder::query()
            ->with(['project', 'supplier', 'quotation', 'items', 'contract', 'approvedByUser', 'cancelledByUser'])
            ->findOrFail($purchaseOrderId);
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
            'selectedOrder.status' => ['required', Rule::in(PurchaseOrderStatus::values())],
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
        $this->approval_notes = '';
        $this->observation = '';
        $this->cancellation_reason = '';
    }
}
