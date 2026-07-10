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

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@open9.dev'],
            [
                'name' => 'Admin Titon',
                'password' => Hash::make('password'),
            ],
        );

        $syncUserCompanies->handle(
            $admin,
            $companies->pluck('id')->all(),
            $companies->mapWithKeys(fn (Company $company) => [$company->id => $roles['Super Admin']->id])->all(),
            $companies->pluck('id')->all(),
            $companies->first()?->id,
        );

        collect(range(1, 9))->each(function (int $number, int $index) use ($companies, $roles, $syncUserCompanies): void {
            $user = User::query()->firstOrCreate(
                ['email' => 'demo'.$number.'@open9.dev'],
                [
                    'name' => 'Usuario Demo '.$number,
                    'password' => Hash::make('password'),
                ],
            );

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
