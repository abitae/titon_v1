<?php

use App\Models\Permission;
use App\Services\Security\PermissionCatalog;
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

        $catalog = app(PermissionCatalog::class);

        foreach (['deployment.ver', 'deployment.editar'] as $permissionName) {
            Permission::query()->updateOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['description' => $catalog->describe($permissionName)],
            );
        }

        $superAdmin = Role::query()
            ->where('name', 'Super Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdmin === null) {
            return;
        }

        $superAdmin->givePermissionTo(
            Permission::query()
                ->whereIn('name', ['deployment.ver', 'deployment.editar'])
                ->where('guard_name', 'web')
                ->pluck('name')
                ->all(),
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
