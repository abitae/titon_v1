<?php

namespace App\Livewire\Purchases;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Actions\Purchases\SyncSupplierQuotationItems;
use App\Concerns\AssignsOperationalCode;
use App\Concerns\InteractsWithToast;
use App\Concerns\SelectsWinningQuotation;
use App\Concerns\ShowsQuotationComparisonModal;
use App\Concerns\ViewsQuotationPdf;
use App\Enums\CorrelativeSubject;
use App\Enums\CurrencyCode;
use App\Enums\QuotationCaptureMode;
use App\Enums\QuotationStatus;
use App\Enums\RequirementStatus;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Services\Purchases\QuotationComparisonSummary;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ManageSupplierQuotations extends Component
{
    use AssignsOperationalCode, InteractsWithToast, SelectsWinningQuotation, ShowsQuotationComparisonModal, ViewsQuotationPdf, WithFileUploads;

    public string $title = 'Cotizaciones por solicitud';

    public PurchaseRequest $purchaseRequest;

    public ?int $editingQuotationId = null;

    public bool $showFormModal = false;

    public string $supplier_id = '';

    public string $code = '';

    public string $quotation_date = '';

    public string $valid_until = '';

    public string $currency = 'PEN';

    public string $tax = '0';

    public string $delivery_time = '1';

    public string $payment_conditions = '';

    public string $warranty = '';

    public string $observation = '';

    public string $capture_mode = 'form';

    public string $subtotal = '0';

    /** @var TemporaryUploadedFile|null */
    public $quotation_pdf = null;

    /**
     * @var list<array<string, mixed>>
     */
    public array $items = [];

    public bool $showItemModal = false;

    public ?int $editingItemIndex = null;

    public string $item_product_or_service = '';

    public string $item_unit = 'und';

    public string $item_quantity = '1';

    public string $item_unit_price = '0';

    public function mount(PurchaseRequest $purchaseRequest): void
    {
        $this->purchaseRequest = $purchaseRequest->load(['project', 'items', 'comparison']);
        $this->bootWinningQuotationSelection($this->purchaseRequest);
        $this->quotation_date = now()->toDateString();
        $this->items = $this->itemsFromRequirement();
    }

    public function render(QuotationComparisonSummary $quotationComparisonSummary): View
    {
        return view('livewire.purchases.manage-supplier-quotations', [
            'quotations' => $this->purchaseRequest->quotations()->with(['supplier', 'items', 'media'])->latest()->get(),
            'suppliers' => Supplier::query()->orderBy('business_name')->get(),
            'currencyOptions' => CurrencyCode::cases(),
            'captureModeOptions' => QuotationCaptureMode::cases(),
            'summary' => $quotationComparisonSummary->build($this->purchaseRequest),
            'comparison' => $this->purchaseRequest->comparison()->with(['selectedQuotation.supplier', 'selectedByUser'])->first(),
            'editingQuotation' => $this->editingQuotationId
                ? $this->purchaseRequest->quotations()->with('media')->find($this->editingQuotationId)
                : null,
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
        $this->supplier_id = (string) $quotation->supplier_id;
        $this->code = $quotation->code;
        $this->quotation_date = $quotation->quotation_date?->format('Y-m-d') ?? '';
        $this->valid_until = $quotation->valid_until?->format('Y-m-d') ?? '';
        $this->currency = $quotation->currency;
        $this->tax = (string) $quotation->tax;
        $this->delivery_time = (string) ($quotation->delivery_time_days ?? 0);
        $this->payment_conditions = $quotation->payment_conditions ?? '';
        $this->warranty = $quotation->warranty ?? '';
        $this->observation = $quotation->observation ?? '';
        $this->capture_mode = $quotation->capture_mode ?? QuotationCaptureMode::Form->value();
        $this->subtotal = (string) $quotation->subtotal;
        $this->quotation_pdf = null;
        $this->items = $quotation->items->map(fn ($item): array => [
            'product_or_service' => $item->product_or_service,
            'unit' => $item->unit,
            'quantity' => (string) $item->quantity,
            'unit_price' => (string) $item->unit_price,
        ])->all();
        $this->showFormModal = true;
    }

    public function openItemModal(?int $index = null): void
    {
        $this->resetItemDraft();
        $this->editingItemIndex = $index;

        if ($index !== null && isset($this->items[$index])) {
            $item = $this->items[$index];
            $this->item_product_or_service = $item['product_or_service'];
            $this->item_unit = $item['unit'];
            $this->item_quantity = (string) $item['quantity'];
            $this->item_unit_price = (string) $item['unit_price'];
        }

        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        $validated = $this->validate([
            'item_product_or_service' => ['required', 'string', 'max:255'],
            'item_unit' => ['required', 'string', 'max:50'],
            'item_quantity' => ['required', 'numeric', 'min:0.01'],
            'item_unit_price' => ['required', 'numeric', 'min:0'],
        ], [], [
            'item_product_or_service' => 'producto o servicio',
            'item_unit' => 'unidad',
            'item_quantity' => 'cantidad',
            'item_unit_price' => 'precio unitario',
        ]);

        $item = [
            'product_or_service' => $validated['item_product_or_service'],
            'unit' => $validated['item_unit'],
            'quantity' => (string) $validated['item_quantity'],
            'unit_price' => (string) $validated['item_unit_price'],
        ];

        if ($this->editingItemIndex !== null) {
            $this->items[$this->editingItemIndex] = $item;
        } else {
            $this->items[] = $item;
        }

        $this->closeItemModal();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->editingItemIndex = null;
        $this->resetItemDraft();
    }

    public function saveQuotation(SyncSupplierQuotationItems $syncSupplierQuotationItems): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingQuotationId ? 'purchases.editar' : 'purchases.crear'), 403);

        $isPdfMode = $this->capture_mode === QuotationCaptureMode::Pdf->value();
        $existingQuotation = $this->editingQuotationId
            ? $this->purchaseRequest->quotations()->find($this->editingQuotationId)
            : null;
        $hasStoredPdf = $existingQuotation?->getFirstMedia('cotizacion_pdf') !== null;

        $rules = [
            'capture_mode' => ['required', Rule::in(QuotationCaptureMode::values())],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'currency' => ['required', Rule::in(CurrencyCode::values())],
            'tax' => ['required', 'numeric', 'min:0'],
            'delivery_time' => ['required', 'integer', 'min:0'],
            'payment_conditions' => ['nullable', 'string'],
            'warranty' => ['nullable', 'string'],
            'observation' => ['nullable', 'string'],
        ];

        if ($isPdfMode) {
            $rules['subtotal'] = ['required', 'numeric', 'min:0'];
            $rules['quotation_pdf'] = $hasStoredPdf
                ? ['nullable', 'file', 'mimes:pdf', 'max:10240']
                : ['required', 'file', 'mimes:pdf', 'max:10240'];
        } else {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.product_or_service'] = ['required', 'string', 'max:255'];
            $rules['items.*.unit'] = ['required', 'string', 'max:50'];
            $rules['items.*.quantity'] = ['required', 'numeric', 'min:0.01'];
            $rules['items.*.unit_price'] = ['required', 'numeric', 'min:0'];
        }

        $validated = $this->validate($rules, [], $this->quotationValidationAttributes());

        $isEditing = $this->editingQuotationId !== null;

        $subtotal = $isPdfMode
            ? (float) $validated['subtotal']
            : collect($validated['items'] ?? [])->sum(fn (array $item): float => (float) $item['quantity'] * (float) $item['unit_price']);
        $tax = (float) $validated['tax'];
        $itemsToSync = $isPdfMode ? [] : ($validated['items'] ?? []);

        $quotation = DB::transaction(function () use ($validated, $company, $subtotal, $tax, $isEditing): SupplierQuotation {
            $project = $this->purchaseRequest->project;
            $existing = $isEditing
                ? SupplierQuotation::query()->find($this->editingQuotationId)
                : null;

            $finalCode = $this->assignOperationalCode(
                $company,
                CorrelativeSubject::SupplierQuotation,
                $project,
                existingCode: $existing?->code,
                isEditing: $isEditing,
            );

            $payload = [
                'company_id' => $company->id,
                'work_project_id' => $this->purchaseRequest->work_project_id,
                'requirement_id' => $this->purchaseRequest->id,
                'supplier_id' => (int) $validated['supplier_id'],
                'code' => $finalCode,
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?: null,
                'currency' => $validated['currency'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'delivery_time_days' => (int) $validated['delivery_time'],
                'payment_conditions' => $validated['payment_conditions'] ?: null,
                'warranty' => $validated['warranty'] ?: null,
                'observation' => $validated['observation'] ?: null,
                'capture_mode' => $validated['capture_mode'],
            ];

            if (! $isEditing) {
                $payload['status'] = QuotationStatus::Registered->value();
            }

            return SupplierQuotation::query()->updateOrCreate(
                ['id' => $this->editingQuotationId],
                $payload,
            );
        });

        $syncSupplierQuotationItems->handle($quotation, $itemsToSync);
        $this->storeQuotationPdf($quotation);

        $this->purchaseRequest->update([
            'status' => RequirementStatus::InProcess->value(),
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
        $this->closeItemModal();
        $this->showFormModal = false;
    }

    protected function resetForm(bool $useRequestItems): void
    {
        $this->closeItemModal();
        $this->reset([
            'editingQuotationId',
            'code',
            'valid_until',
            'payment_conditions',
            'warranty',
            'observation',
            'quotation_pdf',
        ]);

        $this->supplier_id = '';

        $this->capture_mode = QuotationCaptureMode::Form->value();
        $this->quotation_date = now()->toDateString();
        $this->currency = CurrencyCode::PEN->value();
        $this->tax = '0';
        $this->subtotal = '0';
        $this->delivery_time = '1';
        $this->items = $useRequestItems ? $this->itemsFromRequirement() : $this->itemsFromRequirement();
        $this->showFormModal = false;
    }

    /**
     * @return list<array<string, string>>
     */
    protected function itemsFromRequirement(): array
    {
        return $this->purchaseRequest->items->map(fn ($item): array => [
            'product_or_service' => $item->description,
            'unit' => $item->unit,
            'quantity' => (string) $item->quantity,
            'unit_price' => '0',
        ])->all();
    }

    protected function resetItemDraft(): void
    {
        $this->reset([
            'item_product_or_service',
            'item_unit',
            'item_quantity',
            'item_unit_price',
        ]);

        $this->item_unit = 'und';
        $this->item_quantity = '1';
        $this->item_unit_price = '0';
    }

    protected function storeQuotationPdf(SupplierQuotation $quotation): void
    {
        if ($this->quotation_pdf === null) {
            return;
        }

        $quotation->clearMediaCollection('cotizacion_pdf');

        $quotation
            ->addMedia($this->quotation_pdf->getRealPath())
            ->usingFileName($this->quotation_pdf->getClientOriginalName())
            ->usingName(pathinfo($this->quotation_pdf->getClientOriginalName(), PATHINFO_FILENAME))
            ->toMediaCollection('cotizacion_pdf', 'public');

        $this->quotation_pdf = null;
    }

    /**
     * @return array<string, string>
     */
    protected function quotationValidationAttributes(): array
    {
        return [
            'capture_mode' => 'modo de captura',
            'supplier_id' => 'proveedor',
            'quotation_date' => 'fecha de cotización',
            'valid_until' => 'vigencia',
            'currency' => 'moneda',
            'tax' => 'IGV / impuesto',
            'delivery_time' => 'entrega (días)',
            'subtotal' => 'subtotal',
            'quotation_pdf' => 'archivo PDF',
            'items' => 'ítems cotizados',
            'items.*.product_or_service' => 'producto o servicio',
            'items.*.unit' => 'unidad',
            'items.*.quantity' => 'cantidad',
            'items.*.unit_price' => 'precio unitario',
        ];
    }
}
