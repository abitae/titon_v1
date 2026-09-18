<?php

namespace Database\Seeders;

use App\Actions\Purchases\SyncPurchaseRequestItems;
use App\Enums\CorrelativeSubject;
use App\Enums\RequirementStatus;
use App\Models\Company;
use App\Models\CostType;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\User;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Database\Seeder;

class RequirementSeeder extends Seeder
{
    public function run(): void
    {
        $codeGenerator = app(CodeGeneratorService::class);
        $syncItems = app(SyncPurchaseRequestItems::class);

        Company::query()->each(function (Company $company) use ($codeGenerator, $syncItems): void {
            $project = Project::query()->where('company_id', $company->id)->orderBy('id')->first();
            $responsible = $company->users()->wherePivot('active', true)->first()
                ?? User::query()->first();
            $equipmentCostType = CostType::query()
                ->where('company_id', $company->id)
                ->where('code', 'EQ')
                ->first();

            if ($project === null || $responsible === null) {
                return;
            }

            $title = 'Alquiler de compactadora demo';

            if (Requirement::query()->where('company_id', $company->id)->where('title', $title)->exists()) {
                return;
            }

            $requirement = Requirement::query()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'responsible_user_id' => $responsible->id,
                'requested_by' => $responsible->id,
                'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::Requirement),
                'title' => $title,
                'requirement_type' => 'servicio',
                'cost_type_id' => $equipmentCostType?->id,
                'priority' => 'baja',
                'requested_by_name' => $responsible->name,
                'request_date' => now()->subDays(1)->toDateString(),
                'needed_date' => now()->addDays(8)->toDateString(),
                'description' => 'Alquiler de rodillo compactador para bases de pavimento.',
                'observation' => 'Incluir operador, combustible y traslado ida y vuelta.',
                'status' => RequirementStatus::InProcess->value(),
            ]);

            $syncItems->handle($requirement, [
                [
                    'item_type' => 'servicio',
                    'cost_center_ua' => 'UA-EQ-01',
                    'description' => 'Alquiler de rodillo compactador 10 t',
                    'unit' => 'día',
                    'quantity' => '15',
                    'technical_specification' => 'Equipo con operador, 8 horas/día, incluye flete',
                    'estimated_unit_price' => '850',
                    'observation' => 'Disponibilidad inmediata en Lima.',
                ],
            ]);
        });
    }
}
