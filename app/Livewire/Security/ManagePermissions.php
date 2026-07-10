<?php

namespace App\Livewire\Security;

use App\Models\Permission;
use App\Services\Security\PermissionCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ManagePermissions extends Component
{
    public string $title = 'Permisos del sistema';

    public string $search = '';

    public string $moduleFilter = '';

    public function render(): View
    {
        abort_unless(auth()->user()->can('permissions.ver'), 403);

        return view('livewire.security.manage-permissions', [
            'permissions' => $this->permissions(),
            'modules' => $this->availableModules(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    /**
     * @return Collection<int, Permission>
     */
    protected function permissions(): Collection
    {
        return Permission::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($builder): void {
                    $builder
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->moduleFilter !== '', fn ($query) => $query->where('name', 'like', $this->moduleFilter.'.%'))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, string>
     */
    protected function availableModules(): Collection
    {
        $catalog = app(PermissionCatalog::class);

        return Permission::query()
            ->pluck('name')
            ->map(fn (string $name): string => $catalog->moduleFromPermission($name))
            ->unique()
            ->sort()
            ->values();
    }
}
