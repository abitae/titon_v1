<?php

namespace App\Livewire\Requirements;

use App\Actions\Requirements\SendRequirementToSuppliers as SendRequirementToSuppliersAction;
use App\Concerns\InteractsWithToast;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Support\DefaultDate;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SendRequirementToSuppliers extends Component
{
    use InteractsWithToast;

    public string $title = 'Enviar a proveedores';

    public PurchaseRequest $purchaseRequest;

    /** @var list<int> */
    public array $supplier_ids = [];

    public string $message = '';

    public string $response_deadline = '';

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        $this->purchaseRequest = $purchaseRequest->load(['project', 'invitations.supplier']);
        $this->response_deadline = DefaultDate::daysAhead(7);
    }

    public function render(): View
    {
        return view('livewire.requirements.send-requirement-to-suppliers', [
            'suppliers' => Supplier::query()->orderBy('business_name')->get(),
            'invitations' => $this->purchaseRequest->invitations()->with('supplier', 'sender')->latest('sent_at')->get(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function send(SendRequirementToSuppliersAction $action): void
    {
        abort_unless(auth()->user()->can('requerimientos.enviar_proveedor') || auth()->user()->can('purchases.aprobar'), 403);

        $validated = $this->validate([
            'supplier_ids' => ['required', 'array', 'min:1'],
            'supplier_ids.*' => ['integer', Rule::exists('suppliers', 'id')],
            'message' => ['nullable', 'string'],
            'response_deadline' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $action->handle(
            $this->purchaseRequest,
            $validated['supplier_ids'],
            auth()->user(),
            $validated['message'] ?: null,
            $validated['response_deadline'],
        );

        $this->purchaseRequest->refresh();
        $this->reset('supplier_ids', 'message');
        $this->response_deadline = DefaultDate::daysAhead(7);
        $this->successToast('Requerimiento enviado a proveedores.');
    }
}
