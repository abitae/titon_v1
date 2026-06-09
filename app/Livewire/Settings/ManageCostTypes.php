<?php

namespace App\Livewire\Settings;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Models\CostType;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCostTypes extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Tipos de costo';

    public string $search = '';

    public string $activeFilter = 'all';

    public bool $showFormModal = false;

    public ?int $editingCostTypeId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    public function render(): View
    {
        $costTypes = CostType::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($nestedQuery): void {
                    $nestedQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->activeFilter !== 'all', fn ($query) => $query->where('is_active', $this->activeFilter === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.settings.manage-cost-types', [
            'costTypes' => $costTypes,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedActiveFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $costTypeId): void
    {
        $costType = CostType::query()->findOrFail($costTypeId);

        $this->editingCostTypeId = $costType->id;
        $this->name = $costType->name;
        $this->code = $costType->code ?? '';
        $this->description = $costType->description ?? '';
        $this->is_active = $costType->is_active;
        $this->sort_order = $costType->sort_order;
        $this->showFormModal = true;
    }

    public function saveCostType(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingCostTypeId ? 'catalogs.editar' : 'catalogs.crear'), 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cost_types', 'name')
                    ->where(fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($this->editingCostTypeId),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'bool'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $isEditing = $this->editingCostTypeId !== null;

        CostType::query()->updateOrCreate(
            ['id' => $this->editingCostTypeId],
            [
                ...$validated,
                'company_id' => $company->id,
            ],
        );

        $this->resetForm();
        $this->successToast($isEditing ? 'Tipo de costo actualizado correctamente.' : 'Tipo de costo creado correctamente.');
    }

    public function deleteCostType(int $costTypeId): void
    {
        abort_unless(auth()->user()->can('catalogs.eliminar'), 403);

        CostType::query()->findOrFail($costTypeId)->delete();
        $this->resetPage();
        $this->warningToast('Tipo de costo eliminado correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingCostTypeId',
            'name',
            'code',
            'description',
        ]);

        $this->is_active = true;
        $this->sort_order = 0;
        $this->showFormModal = false;
    }
}
