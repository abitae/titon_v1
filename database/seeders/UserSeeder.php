<?php

namespace Database\Seeders;

use App\Actions\Users\SyncUserCompanies;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::query()->orderBy('id')->get();
        $roles = Role::query()->whereNull('company_id')->get()->keyBy('name');
        $syncUserCompanies = app(SyncUserCompanies::class);

        $admin = User::query()->create([
            'name' => 'Admin Titon',
            'email' => 'admin@titon.com',
            'password' => Hash::make('password'),
        ]);

        $syncUserCompanies->handle(
            $admin,
            $companies->pluck('id')->all(),
            $companies->mapWithKeys(fn (Company $company) => [$company->id => $roles['Super Admin']->id])->all(),
            $companies->pluck('id')->all(),
            $companies->first()?->id,
        );

        User::factory(9)->create()->each(function (User $user, int $index) use ($companies, $roles, $syncUserCompanies): void {
            $maxCompanies = max(1, $companies->count());
            $assignedCompanies = $companies->shuffle()->take(random_int(1, $maxCompanies))->values();
            $roleNames = ['Gerencia', 'Administrador', 'Compras', 'Finanzas', 'Responsable de Obra', 'Consulta'];

            $roleIds = $assignedCompanies->mapWithKeys(function (Company $company) use ($roleNames, $roles, $index): array {
                $roleName = $roleNames[$index % count($roleNames)];

                return [$company->id => $roles[$roleName]->id];
            })->all();

            $syncUserCompanies->handle(
                $user,
                $assignedCompanies->pluck('id')->all(),
                $roleIds,
                $assignedCompanies->pluck('id')->all(),
                $assignedCompanies->first()?->id,
            );
        });
    }
}
