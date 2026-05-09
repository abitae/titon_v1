<?php

namespace App\Livewire\Purchases;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Purchases\SyncSupplierQuotationItems;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Enums\CurrencyCode;
use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageSupplierQuotations extends Component
{
    use InteractsWithToast;

    public string $title = 'Cotizaciones por solicitud';

    public PurchaseRequest $purchaseRequest;

    public ?int $editingQuotationId = null;

    public bool $showFormModal = false;

    public ?int $supplier_id = null;

    public string $code = '';

    public string $quotation_date = '';

    public string $valid_until = '';

    public string $currency = 'PEN';

    public string $tax = '0';

    public string $delivery_time = '1';

    public string $payment_conditions = '';

    public string $warranty = '';

    public string $observation = '';

    /**
     * @var list<array<string, mixed>>
     */
    public array $items = [];

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        $this->purchaseRequest = $purchaseRequest->load(['project', 'items', 'comparison']);
        $this->quotation_date = now()->toDateString();
        $this->items = $this->purchaseRequest->items->map(fn ($item): array => [
            'product_or_service' => $item->product_or_service,
            'unit' => $item->unit,
            'quantity' => (string) $item->quantity,
            'unit_price' => '0',
        ])->all();

        if ($this->items === []) {
            $this->items = [$this->emptyItem()];
        }
    }

    public function render(): View
    {
        return view('livewire.purchases.manage-supplier-quotations', [
            'quotations' => $this->purchaseRequest->quotations()->with(['supplier', 'items'])->latest()->get(),
            'suppliers' => Supplier::query()->orderBy('business_name')->get(),
            'currencyOptions' => CurrencyCode::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openCreateModal(): void
    {
        $this->resetForm(false);
        $this->showFormModal = true;
    }

    public function openEditModal(int $quotationId): void
    {
        $quotation = $this->purchaseRequest->quotations()->with('items')->findOrFail($quotationId);

        $this->editingQuotationId = $quotation->id;
        $this->supplier_id = $quotation->supplier_id;
        $this->code = $quotation->code;
        $this->quotation_date = $quotation->quotation_date?->format('Y-m-d') ?? '';
        $this->valid_until = $quotation->valid_until?->format('Y-m-d') ?? '';
        $this->currency = $quotation->currency;
        $this->tax = (string) $quotation->tax;
        $this->delivery_time = (string) $quotation->delivery_time;
        $this->payment_conditions = $quotation->payment_conditions ?? '';
        $this->warranty = $quotation->warranty ?? '';
        $this->observation = $quotation->observation ?? '';
        $this->items = $quotation->items->map(fn ($item): array => [
            'product_or_service' => $item->product_or_service,
            'unit' => $item->unit,
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) $item->unit_price,
        ])->all();
        $this->showFormModal = true;
    }

    public function addItem(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items[] = $this->emptyItem();
        }
    }

    public function saveQuotation(SyncSupplierQuotationItems $syncSupplierQuotationItems): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingQuotationId ? 'purchases.editar' : 'purchases.crear'), 403);

        $validated = $this->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'code' => [
                Rule::requiredIf($this->editingQuotationId !== null),
                'nullable',
                'string',
                'max:50',
                Rule::unique('supplier_quotations', 'code')
                    ->where(fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($this->editingQuotationId),
            ],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'currency' => ['required', Rule::in(CurrencyCode::values())],
            'tax' => ['required', 'numeric', 'min:0'],
            'delivery_time' => ['required', 'integer', 'min:0'],
            'payment_conditions' => ['nullable', 'string'],
            'warranty' => ['nullable', 'string'],
            'observation' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_or_service' => ['required', 'string', 'max:255'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
        $isEditing = $this->editingQuotationId !== null;

        $subtotal = collect($validated['items'])->sum(fn (array $item): float => (float) $item['quantity'] * (float) $item['unit_price']);
        $tax = (float) $validated['tax'];

        $quotation = DB::transaction(function () use ($validated, $company, $subtotal, $tax, $isEditing): SupplierQuotation {
            $finalCode = trim((string) ($validated['code'] ?? ''));

            if (! $isEditing && $finalCode === '') {
                $finalCode = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::SupplierQuotation);
            }

            return SupplierQuotation::query()->updateOrCreate(
                ['id' => $this->editingQuotationId],
                [
                    'company_id' => $company->id,
                    'work_project_id' => $this->purchaseRequest->work_project_id,
                    'purchase_request_id' => $this->purchaseRequest->id,
                    'supplier_id' => $validated['supplier_id'],
                    'code' => $isEditing ? $validated['code'] : $finalCode,
                    'quotation_date' => $validated['quotation_date'],
                    'valid_until' => $validated['valid_until'] ?: null,
                    'currency' => $validated['currency'],
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $subtotal + $tax,
                    'delivery_time' => $validated['delivery_time'],
                    'payment_conditions' => $validated['payment_conditions'] ?: null,
                    'warranty' => $validated['warranty'] ?: null,
                    'observation' => $validated['observation'] ?: null,
                ],
            );
        });

        $syncSupplierQuotationItems->handle($quotation, $validated['items']);

        $this->purchaseRequest->update([
            'status' => PurchaseRequestStatus::Quoted->value(),
        ]);

        $this->purchaseRequest->refresh();
        $this->resetForm(true);
        $this->successToast($isEditing ? 'Cotizacion actualizada correctamente.' : 'Cotizacion registrada correctamente.');
    }

    public function deleteQuotation(int $quotationId): void
    {
        abort_unless(auth()->user()->can('purchases.eliminar'), 403);

        $this->purchaseRequest->quotations()->findOrFail($quotationId)->delete();
        $this->purchaseRequest->refresh();
        $this->warningToast('Cotizacion eliminada correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(bool $useRequestItems): void
    {
        $this->reset([
            'editingQuotationId',
            'supplier_id',
            'code',
            'valid_until',
            'payment_conditions',
            'warranty',
            'observation',
        ]);

        $this->quotation_date = now()->toDateString();
        $this->currency = CurrencyCode::PEN->value();
        $this->tax = '0';
        $this->delivery_time = '1';
        $this->items = $useRequestItems
            ? $this->purchaseRequest->items->map(fn ($item): array => [
                'product_or_service' => $item->product_or_service,
                'unit' => $item->unit,
                'quantity' => (string) $item->quantity,
                'unit_price' => '0',
            ])->all()
            : ($this->purchaseRequest->items->map(fn ($item): array => [
                'product_or_service' => $item->product_or_service,
                'unit' => $item->unit,
                'quantity' => (string) $item->quantity,
                'unit_price' => '0',
            ])->all() ?: [$this->emptyItem()]);
        $this->showFormModal = false;
    }

    /**
     * @return array<string, string>
     */
    protected function emptyItem(): array
    {
        return [
            'product_or_service' => '',
            'unit' => 'und',
            'quantity' => '1',
            'unit_price' => '0',
        ];
    }
}
