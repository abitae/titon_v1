<?php

namespace App\Livewire\Security;

use App\Concerns\InteractsWithToast;
use App\Models\Permission;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class ManageRoles extends Component
{
    use InteractsWithToast;

    public string $title = 'Roles del sistema';

    public string $search = '';

    public bool $showEditModal = false;

    public ?int $editingRoleId = null;

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function render(): View
    {
        abort_unless(auth()->user()->can('roles.ver'), 403);

        return view('livewire.security.manage-roles', [
            'roles' => $this->roles(),
            'permissionGroups' => $this->permissionGroups(),
            'canEdit' => auth()->user()->can('roles.editar'),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function editRole(int $roleId): void
    {
        abort_unless(auth()->user()->can('roles.editar'), 403);

        $role = Role::query()->findOrFail($roleId);

        if ($role->name === 'Super Admin') {
            $this->dangerToast('El rol Super Admin no puede modificarse.');

            return;
        }

        $this->editingRoleId = $role->id;
        $this->selectedPermissions = $role->permissions()->pluck('name')->all();
        $this->showEditModal = true;
    }

    public function saveRolePermissions(): void
    {
        abort_unless(auth()->user()->can('roles.editar'), 403);

        if ($this->editingRoleId === null) {
            return;
        }

        $role = Role::query()->findOrFail($this->editingRoleId);

        if ($role->name === 'Super Admin') {
            $this->dangerToast('El rol Super Admin no puede modificarse.');

            return;
        }

        $role->syncPermissions($this->selectedPermissions);

        $this->successToast('Permisos del rol actualizados correctamente.');
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->showEditModal = false;
        $this->editingRoleId = null;
        $this->selectedPermissions = [];
    }

    /**
     * @return Collection<int, Role>
     */
    protected function roles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->withCount('permissions')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    protected function permissionGroups(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => explode('.', $permission->name, 2)[0]);
    }
}
