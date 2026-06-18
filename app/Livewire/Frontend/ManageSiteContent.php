<?php

namespace App\Livewire\Frontend;

use App\Concerns\InteractsWithToast;
use App\Models\SiteSetting;
use App\Services\Frontend\SiteContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ManageSiteContent extends Component
{
    use InteractsWithToast, WithFileUploads;

    public string $title = 'Contenido del sitio web';

    public string $selectedGroup = 'home';

    public ?int $editingSettingId = null;

    public string $key = '';

    public string $sectionTitle = '';

    public string $subtitle = '';

    public string $body = '';

    public string $cta_label = '';

    public string $cta_url = '';

    public ?TemporaryUploadedFile $image = null;

    public ?string $currentImageUrl = null;

    public bool $is_active = true;

    public int $sort_order = 0;

    public ?int $brandSettingId = null;

    public string $brandName = '';

    public ?TemporaryUploadedFile $brandLogo = null;

    public ?TemporaryUploadedFile $brandFavicon = null;

    public ?string $currentBrandLogoUrl = null;

    public ?string $currentBrandFaviconUrl = null;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    public function render(): View
    {
        $settings = $this->selectedGroup === 'brand'
            ? collect()
            : SiteSetting::query()
                ->where('key', 'like', $this->selectedGroup.'.%')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

        return view('livewire.frontend.manage-site-content', [
            'settings' => $settings,
            'groups' => [
                'brand' => 'Marca',
                'home' => 'Inicio',
                'about' => 'Nosotros',
                'projects' => 'Proyectos',
                'contact' => 'Contacto',
            ],
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function selectGroup(string $group): void
    {
        $this->selectedGroup = $group;
        $this->resetForm();

        if ($group === 'brand') {
            $this->loadBrandForm();
        }
    }

    public function openEditModal(int $settingId): void
    {
        $setting = SiteSetting::query()->findOrFail($settingId);

        $this->editingSettingId = $setting->id;
        $this->key = $setting->key;
        $this->sectionTitle = $setting->title ?? '';
        $this->subtitle = $setting->subtitle ?? '';
        $this->body = $setting->body ?? '';
        $this->cta_label = $setting->cta_label ?? '';
        $this->cta_url = $setting->cta_url ?? '';
        $this->currentImageUrl = $setting->imageUrl();
        $this->is_active = $setting->is_active;
        $this->sort_order = $setting->sort_order;
        $this->image = null;
    }

    public function saveSetting(SiteContentService $content): void
    {
        $this->authorizeSuperAdmin();

        $validated = $this->validate([
            'sectionTitle' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'bool'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $setting = SiteSetting::query()->findOrFail($this->editingSettingId);
        $imagePath = $setting->image_path;

        if ($this->image instanceof TemporaryUploadedFile) {
            if (filled($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $this->image->store('site', 'public');
        }

        $setting->update([
            'title' => $validated['sectionTitle'] ?: null,
            'subtitle' => $validated['subtitle'] ?: null,
            'body' => $validated['body'] ?: null,
            'cta_label' => $validated['cta_label'] ?: null,
            'cta_url' => $validated['cta_url'] ?: null,
            'image_path' => $imagePath,
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        $content->forgetSection($setting->key);

        $this->resetForm();
        $this->successToast('Contenido actualizado correctamente.');
    }

    public function saveBrand(SiteContentService $content): void
    {
        $this->authorizeSuperAdmin();

        $validated = $this->validate([
            'brandName' => ['nullable', 'string', 'max:120'],
            'brandLogo' => ['nullable', 'image', 'max:4096'],
            'brandFavicon' => ['nullable', 'image', 'max:1024', 'mimes:png,ico,svg,jpg,jpeg,webp'],
        ]);

        $brand = SiteSetting::query()->firstOrCreate(
            ['key' => 'brand'],
            ['is_active' => true, 'sort_order' => 0],
        );

        $logoPath = $brand->image_path;
        $faviconPath = $brand->favicon_path;

        if ($this->brandLogo instanceof TemporaryUploadedFile) {
            if (filled($logoPath) && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $this->brandLogo->store('site/brand', 'public');
        }

        if ($this->brandFavicon instanceof TemporaryUploadedFile) {
            if (filled($faviconPath) && Storage::disk('public')->exists($faviconPath)) {
                Storage::disk('public')->delete($faviconPath);
            }

            $faviconPath = $this->brandFavicon->store('site/brand', 'public');
        }

        $brand->update([
            'title' => $validated['brandName'] ?: null,
            'image_path' => $logoPath,
            'favicon_path' => $faviconPath,
            'is_active' => true,
        ]);

        $content->forgetSection('brand');

        $this->loadBrandForm();
        $this->brandLogo = null;
        $this->brandFavicon = null;

        $this->successToast('Marca actualizada correctamente.');
    }

    public function removeBrandLogo(SiteContentService $content): void
    {
        $this->authorizeSuperAdmin();

        $brand = SiteSetting::query()->where('key', 'brand')->first();

        if ($brand === null) {
            return;
        }

        if (filled($brand->image_path) && Storage::disk('public')->exists($brand->image_path)) {
            Storage::disk('public')->delete($brand->image_path);
        }

        $brand->update(['image_path' => null]);
        $content->forgetSection('brand');

        $this->brandLogo = null;
        $this->loadBrandForm();

        $this->successToast('Logotipo eliminado correctamente.');
    }

    public function removeBrandFavicon(SiteContentService $content): void
    {
        $this->authorizeSuperAdmin();

        $brand = SiteSetting::query()->where('key', 'brand')->first();

        if ($brand === null) {
            return;
        }

        if (filled($brand->favicon_path) && Storage::disk('public')->exists($brand->favicon_path)) {
            Storage::disk('public')->delete($brand->favicon_path);
        }

        $brand->update(['favicon_path' => null]);
        $content->forgetSection('brand');

        $this->brandFavicon = null;
        $this->loadBrandForm();

        $this->successToast('Favicon eliminado correctamente.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    protected function loadBrandForm(): void
    {
        $brand = SiteSetting::query()->firstOrCreate(
            ['key' => 'brand'],
            ['is_active' => true, 'sort_order' => 0],
        );

        $this->brandSettingId = $brand->id;
        $this->brandName = $brand->title ?? '';
        $this->currentBrandLogoUrl = $brand->imageUrl();
        $this->currentBrandFaviconUrl = $brand->faviconUrl();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingSettingId',
            'key',
            'sectionTitle',
            'subtitle',
            'body',
            'cta_label',
            'cta_url',
            'image',
            'currentImageUrl',
            'brandSettingId',
            'brandName',
            'brandLogo',
            'brandFavicon',
            'currentBrandLogoUrl',
            'currentBrandFaviconUrl',
        ]);

        $this->is_active = true;
        $this->sort_order = 0;
    }

    protected function authorizeSuperAdmin(): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);
    }
}
