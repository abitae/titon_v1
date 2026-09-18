<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CostType;
use Illuminate\Database\Seeder;

class CostTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Materiales', 'code' => 'MAT', 'description' => 'Insumos, materiales de construcción y consumibles de obra.', 'sort_order' => 1],
            ['name' => 'Mano de obra', 'code' => 'MO', 'description' => 'Personal propio, jornales y supervision de campo.', 'sort_order' => 2],
            ['name' => 'Equipos', 'code' => 'EQ', 'description' => 'Alquiler, depreciacion y operacion de maquinaria.', 'sort_order' => 3],
            ['name' => 'Servicios', 'code' => 'SER', 'description' => 'Servicios especializados, ensayos y consultoria.', 'sort_order' => 4],
            ['name' => 'Subcontratos', 'code' => 'SUB', 'description' => 'Partidas ejecutadas por terceros bajo contrato.', 'sort_order' => 5],
        ];

        Company::query()->each(function (Company $company) use ($types): void {
            foreach ($types as $type) {
                CostType::withoutGlobalScopes()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $type['code'],
                    ],
                    [
                        'name' => $type['name'],
                        'description' => $type['description'],
                        'is_active' => true,
                        'sort_order' => $type['sort_order'],
                    ],
                );
            }
        });
    }
}
