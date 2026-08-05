<?php

use App\Services\Security\RolePermissionMatrix;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $definitions = RolePermissionMatrix::definitions();

        foreach (RolePermissionMatrix::syncableRoles() as $roleName) {
            $permissions = $definitions[$roleName] ?? null;

            if ($permissions === null) {
                continue;
            }

            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role === null) {
                continue;
            }

            $role->syncPermissions($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
