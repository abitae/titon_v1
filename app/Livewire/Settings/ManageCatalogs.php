<?php

namespace App\Livewire\Settings;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CatalogType;
use App\Models\CatalogItem;
use App\Services\Application\ApplicationSettingsManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageCatalogs extends Component
{
    use InteractsWithToast, WithFileUploads, WithPagination;

    public string $title = 'Configuracion General';

    public string $application_name = '';

    public ?TemporaryUploadedFile $application_logo = null;

    public ?string $currentApplicationLogoUrl = null;

    public string $selectedType = 'cities';

    public string $selectedGroup = 'general';

    public string $search = '';

    public string $activeFilter = 'all';

    public bool $showFormModal = false;

    public ?int $editingCatalogId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

    public function mount(ApplicationSettingsManager $applicationSettings): void
    {
        $this->selectedType = CatalogType::City->value();
        $this->selectedGroup = CatalogType::City->group();
        $this->loadApplicationBranding($applicationSettings);
    }

    public function saveApplicationBranding(ApplicationSettingsManager $applicationSettings): void
    {
        abort_unless(auth()->user()->can('catalogs.editar'), 403);

        $validated = $this->validateWithToastFeedback([
            'application_name' => ['required', 'string', 'max:120'],
            'application_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ], [], [
            'application_name' => 'nombre de la aplicacion',
            'application_logo' => 'icono de la aplicacion',
        ]);

        $settings = $applicationSettings->current();
        $logoPath = $settings->logo_path;

        if ($this->application_logo instanceof TemporaryUploadedFile) {
            if (filled($logoPath) && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $this->application_logo->store('application-settings', 'public');
        }

        $applicationSettings->update([
            'application_name' => $validated['application_name'],
            'logo_path' => $logoPath,
        ]);

        $this->application_logo = null;
        $this->loadApplicationBranding($applicationSettings);
        $this->successToast('Identidad de la aplicacion actualizada.');
    }

    public function removeApplicationLogo(ApplicationSettingsManager $applicationSettings): void
    {
        abort_unless(auth()->user()->can('catalogs.editar'), 403);

        $applicationSettings->removeLogo();
        $this->application_logo = null;
        $this->loadApplicationBranding($applicationSettings);
        $this->warningToast('Icono de la aplicacion eliminado.');
    }

    public function render(): View
    {
        $items = CatalogItem::query()
            ->ofType($this->selectedType)
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

        return view('livewire.settings.manage-catalogs', [
            'items' => $items,
            'groups' => CatalogType::groups(),
            'activeGroupTypes' => CatalogType::forGroup($this->selectedGroup),
            'selectedTypeLabel' => CatalogType::fromValue($this->selectedType)->label(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function selectGroup(string $group): void
    {
        if (! array_key_exists($group, CatalogType::groups())) {
            return;
        }

        $this->selectedGroup = $group;
        $this->selectedType = CatalogType::forGroup($group)[0]->value();
        $this->resetPage();
    }

    public function selectType(string $type): void
    {
        $this->selectedType = CatalogType::fromValue($type)->value();
        $this->selectedGroup = CatalogType::fromValue($type)->group();
        $this->resetPage();
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

    public function openEditModal(int $catalogId): void
    {
        $item = CatalogItem::query()->findOrFail($catalogId);

        $this->editingCatalogId = $item->id;
        $this->name = $item->name;
        $this->code = $item->code ?? '';
        $this->description = $item->description ?? '';
        $this->is_active = $item->is_active;
        $this->sort_order = $item->sort_order;
        $this->showFormModal = true;
    }

    public function saveCatalogItem(): void
    {
        $company = app(ResolveCurrentCompany::class)->handle(auth()->user());

        abort_if($company === null, 403);
        abort_unless(auth()->user()->can($this->editingCatalogId ? 'catalogs.editar' : 'catalogs.crear'), 403);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('catalog_items', 'name')
                    ->where(fn ($query) => $query
                        ->where('company_id', $company->id)
                        ->where('type', $this->selectedType))
                    ->ignore($this->editingCatalogId),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'bool'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
        $isEditing = $this->editingCatalogId !== null;

        CatalogItem::query()->updateOrCreate(
            ['id' => $this->editingCatalogId],
            [
                ...$validated,
                'company_id' => $company->id,
                'type' => $this->selectedType,
            ],
        );

        $this->resetForm();
        $this->successToast($isEditing ? 'Catalogo actualizado correctamente.' : 'Catalogo creado correctamente.');
    }

    public function deleteCatalogItem(int $catalogId): void
    {
        abort_unless(auth()->user()->can('catalogs.eliminar'), 403);

        CatalogItem::query()->findOrFail($catalogId)->delete();
        $this->resetPage();
        $this->warningToast('Catalogo eliminado correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingCatalogId',
            'name',
            'code',
            'description',
        ]);

        $this->is_active = true;
        $this->sort_order = 0;
        $this->showFormModal = false;
    }

    protected function loadApplicationBranding(ApplicationSettingsManager $applicationSettings): void
    {
        $settings = $applicationSettings->current();

        $this->application_name = $settings->application_name;
        $this->currentApplicationLogoUrl = $settings->logoUrl();
    }
}
