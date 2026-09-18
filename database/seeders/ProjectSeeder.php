<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            $responsible = $company->users()->wherePivot('active', true)->first()
                ?? User::query()->first();

            if ($responsible === null) {
                return;
            }

            Project::withoutGlobalScopes()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'code' => sprintf('OBR%03d-02', $company->id),
                ],
                [
                    'name' => 'Ampliacion planta '.$company->name,
                    'city' => 'Arequipa',
                    'address' => 'Parque industrial Lote 12, Arequipa',
                    'client_name' => 'Consorcio Andino del Sur',
                    'responsible_user_id' => $responsible->id,
                    'start_date' => now()->addMonth()->toDateString(),
                    'estimated_end_date' => now()->addMonths(14)->toDateString(),
                    'estimated_budget' => 1850000,
                    'status' => ProjectStatus::Planned->value(),
                    'description' => 'Segunda obra demo con presupuesto, cliente, fechas y responsable completos.',
                ],
            );
        });
    }
}
