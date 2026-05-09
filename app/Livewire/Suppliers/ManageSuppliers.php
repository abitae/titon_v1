<?php

namespace App\Livewire\Suppliers;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Enums\SupplierStatus;
use App\Models\CatalogItem;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageSuppliers extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Proveedores';

    public string $search = '';

    public string $cityFilter = '';

    public string $statusFilter = '';

    public bool $showFormModal = false;

    public bool $showDetailModal = false;

    public ?int $editingSupplierId = null;

    public ?Supplier $selectedSupplier = null;

    public array $attachments = [];

    public string $ruc = '';

    public string $business_name = '';

    public string $commercial_name = '';

    public string $contact_name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $city = '';

    public string $bank_name = '';

    public string $bank_account = '';

    public string $cci = '';

    public string $status = 'active';

    public function render(): View
    {
        $suppliers = Supplier::query()
            ->with('attachments')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('ruc', 'like', '%'.$this->search.'%')
                        ->orWhere('business_name', 'like', '%'.$this->search.'%')
                        ->orWhere('commercial_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->cityFilter !== '', fn ($query) => $query->where('city', $this->cityFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.suppliers.manage-suppliers', [
            'suppliers' => $suppliers,
            'cities' => CatalogItem::query()->ofType(CatalogType::City)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'banks' => CatalogItem::query()->ofType(CatalogType::Bank)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'statusOptions' => SupplierStatus::cases(),
            'summary' => [
                'total' => Supplier::query()->count(),
                'active' => Supplier::query()->where('status', SupplierStatus::Active->value())->count(),
                'inactive' => Supplier::query()->where('status', SupplierStatus::Inactive->value())->count(),
            ],
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $supplierId): void
    {
        $supplier = Supplier::query()->findOrFail($supplierId);

        $this->editingSupplierId = $supplier->id;
        $this->ruc = $supplier->ruc;
        $this->business_name = $supplier->business_name;
        $this->commercial_name = $supplier->commercial_name ?? '';
        $this->contact_name = $supplier->contact_name ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->email = $supplier->email ?? '';
        $this->address = $supplier->address ?? '';
        $this->city = $supplier->city ?? '';
        $this->bank_name = $supplier->bank_name ?? '';
        $this->bank_account = $supplier->bank_account ?? '';
        $this->cci = $supplier->cci ?? '';
        $this->status = $supplier->status;
        $this->attachments = [];
        $this->showFormModal = true;
    }

    public function openDetailModal(int $supplierId): void
    {
        $this->selectedSupplier = Supplier::query()->with('attachments')->findOrFail($supplierId);
        $this->showDetailModal = true;
    }

    public function saveSupplier(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingSupplierId ? 'suppliers.editar' : 'suppliers.crear'), 403);

        $validated = $this->validate($this->rules($company->id));
        $isEditing = $this->editingSupplierId !== null;

        $supplier = Supplier::query()->updateOrCreate(
            ['id' => $this->editingSupplierId],
            [
                ...$validated,
                'company_id' => $company->id,
            ],
        );

        $this->storeAttachments($supplier);
        $this->resetForm();
        $this->successToast($isEditing ? 'Proveedor actualizado correctamente.' : 'Proveedor creado correctamente.');
    }

    public function deleteSupplier(int $supplierId): void
    {
        abort_unless(auth()->user()->can('suppliers.eliminar'), 403);

        Supplier::query()->findOrFail($supplierId)->delete();
        $this->resetPage();
        $this->warningToast('Proveedor eliminado correctamente.');
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showDetailModal = false;
        $this->selectedSupplier = null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(int $companyId): array
    {
        return [
            'ruc' => [
                'required',
                'string',
                'size:11',
                Rule::unique('suppliers', 'ruc')
                    ->where(fn ($query) => $query->where('company_id', $companyId))
                    ->ignore($this->editingSupplierId),
            ],
            'business_name' => ['required', 'string', 'max:255'],
            'commercial_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'cci' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(SupplierStatus::values())],
            'attachments.*' => ['nullable', 'file', 'max:10240'],
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingSupplierId',
            'attachments',
            'ruc',
            'business_name',
            'commercial_name',
            'contact_name',
            'phone',
            'email',
            'address',
            'city',
            'bank_name',
            'bank_account',
            'cci',
        ]);

        $this->status = SupplierStatus::Active->value();
        $this->showFormModal = false;
    }

    protected function storeAttachments(Supplier $supplier): void
    {
        foreach ($this->attachments as $uploadedFile) {
            $path = $uploadedFile->store('attachments/suppliers', 'public');

            $supplier->attachments()->create([
                'company_id' => $supplier->company_id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
            ]);
        }
    }
}
