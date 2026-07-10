<?php

namespace App\Livewire\Mechanics;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Models\CatalogItem;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ManageFleetEquipmentTypes extends Component
{
    use InteractsWithToast, WithPagination;

    public string $title = 'Tipos de equipo';

    public string $search = '';

    public string $activeFilter = 'all';

    public bool $showFormModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('equipos.ver'), 403);
    }

    public function render(): View
    {
        return view('livewire.mechanics.manage-fleet-equipment-types', [
            'types' => CatalogItem::query()
                ->ofType(CatalogType::EquipmentType)
                ->when($this->search !== '', function ($query): void {
                    $query->where(function ($nested): void {
                        $nested->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('code', 'like', '%'.$this->search.'%');
                    });
                })
                ->when($this->activeFilter !== 'all', fn ($query) => $query->where('is_active', $this->activeFilter === 'active'))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(12),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('equipos.crear'), 403);
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('equipos.editar'), 403);
        $item = CatalogItem::query()->ofType(CatalogType::EquipmentType)->findOrFail($id);

        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->code = $item->code ?? '';
        $this->description = $item->description ?? '';
        $this->is_active = $item->is_active;
        $this->sort_order = $item->sort_order;
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());
        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingId ? 'equipos.editar' : 'equipos.crear'), 403);

        $validated = $this->validateWithToastFeedback([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('catalog_items', 'name')
                    ->where(fn ($query) => $query
                        ->where('company_id', $company->id)
                        ->where('type', CatalogType::EquipmentType->value()))
                    ->ignore($this->editingId),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [
            'name.required' => 'Ingrese el nombre del tipo de equipo.',
            'name.unique' => 'Ya existe un tipo de equipo con ese nombre.',
            'sort_order.min' => 'El orden no puede ser negativo.',
        ], [
            'name' => 'nombre',
            'code' => 'codigo',
            'description' => 'descripcion',
            'sort_order' => 'orden',
        ]);

        $isEditing = $this->editingId !== null;

        CatalogItem::query()->updateOrCreate(
            ['id' => $this->editingId],
            [
                ...$validated,
                'company_id' => $company->id,
                'type' => CatalogType::EquipmentType->value(),
            ],
        );

        $this->resetForm();
        $this->successToast(
            $isEditing ? 'Tipo de equipo actualizado correctamente.' : 'Tipo de equipo creado correctamente.',
            $isEditing ? 'Cambios guardados' : 'Registro exitoso',
        );
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('equipos.eliminar'), 403);

        $item = CatalogItem::query()->ofType(CatalogType::EquipmentType)->findOrFail($id);
        $label = $item->name;
        $item->delete();
        $this->resetPage();
        $this->warningToast("Se elimino el tipo \"{$label}\".", 'Tipo eliminado');
    }

    public function close(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'code',
            'description',
        ]);
        $this->is_active = true;
        $this->sort_order = 0;
        $this->showFormModal = false;
    }
}
