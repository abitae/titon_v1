<?php

namespace Database\Seeders;

use App\Enums\CatalogType;
use App\Enums\DocumentPriority;
use App\Enums\FleetCorrectiveMaintenanceStatus;
use App\Enums\FleetEquipmentOperationalStatus;
use App\Enums\FleetPreventiveMaintenanceStatus;
use App\Enums\FleetSparePartMovementDirection;
use App\Enums\FleetSparePartStatus;
use App\Enums\FleetTechnicalInspectionStatus;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use App\Models\FleetSparePart;
use App\Models\FleetSparePartMovement;
use App\Models\FleetTechnicalInspection;
use App\Models\FleetWorkOrder;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class MechanicsSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            if (FleetEquipment::withoutGlobalScopes()->where('company_id', $company->id)->exists()) {
                return;
            }

            $project = Project::query()->where('company_id', $company->id)->first();
            $responsible = $company->users()->wherePivot('active', true)->first()
                ?? User::query()->first();
            $supplier = Supplier::query()->where('company_id', $company->id)->first();

            if ($project === null || $responsible === null) {
                return;
            }

            $equipments = $this->seedEquipments($company, $project, $responsible);
            $this->seedInspections($company, $equipments, $responsible);
            $preventiveRows = $this->seedPreventiveMaintenances($company, $equipments, $responsible);
            $correctiveRows = $this->seedCorrectiveMaintenances($company, $equipments, $responsible);
            $workOrders = $this->seedWorkOrders($company, $project, $equipments, $responsible, $preventiveRows, $correctiveRows);
            $this->seedSpareParts($company, $project, $supplier, $workOrders, $responsible);
        });
    }

    /**
     * @return Collection<int, FleetEquipment>
     */
    protected function seedEquipments(Company $company, Project $project, User $responsible)
    {
        $definitions = [
            ['EQ-001', 'Retroexcavadora CAT 320', 'Retroexcavadora', FleetEquipmentOperationalStatus::Operational, 45200, 3200],
            ['EQ-002', 'Camion volquete Hino', 'Camion', FleetEquipmentOperationalStatus::Operational, 128500, 4100],
            ['EQ-003', 'Generador Perkins 250kVA', 'Generador', FleetEquipmentOperationalStatus::InMaintenance, 0, 1850],
            ['EQ-004', 'Mezcladora de concreto', 'Equipo menor', FleetEquipmentOperationalStatus::Broken, 0, 620],
            ['EQ-005', 'Rodillo compactador', 'Compactacion', FleetEquipmentOperationalStatus::Operational, 8900, 980],
        ];

        return collect($definitions)->map(function (array $definition) use ($company, $project, $responsible): FleetEquipment {
            [$code, $name, $typeName, $status, $km, $hours] = $definition;

            $equipmentType = CatalogItem::withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'type' => CatalogType::EquipmentType->value(),
                    'name' => $typeName,
                ],
                [
                    'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $typeName) ?: 'TIPO', 0, 6)),
                    'is_active' => true,
                ],
            );

            return FleetEquipment::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'responsible_user_id' => $responsible->id,
                'internal_code' => $code,
                'equipment_type_id' => $equipmentType->id,
                'equipment_type' => $equipmentType->name,
                'name' => $name,
                'brand' => 'Demo',
                'model' => 'Serie demo',
                'plate' => 'A'.substr($code, -3, 3).'-X01',
                'year' => 2022,
                'city' => 'Lima',
                'operational_status' => $status->value(),
                'odometer_km' => $km,
                'hour_meter' => $hours,
                'acquisition_date' => now()->subYears(2)->toDateString(),
                'observations' => 'Equipo demo para modulo de mecanica.',
            ]);
        });
    }

    /**
     * @param  Collection<int, FleetEquipment>  $equipments
     */
    protected function seedInspections(Company $company, $equipments, User $responsible): void
    {
        $statuses = [
            FleetTechnicalInspectionStatus::Valid,
            FleetTechnicalInspectionStatus::DueSoon,
            FleetTechnicalInspectionStatus::Expired,
            FleetTechnicalInspectionStatus::Observed,
        ];

        $equipments->each(function (FleetEquipment $equipment, int $index) use ($company, $responsible, $statuses): void {
            FleetTechnicalInspection::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'fleet_equipment_id' => $equipment->id,
                'responsible_user_id' => $responsible->id,
                'reviewed_at' => now()->subYears(2)->addMonths($index)->toDateString(),
                'due_at' => now()->subYear()->addMonths($index)->toDateString(),
                'result' => 'Aprobado',
                'inspection_center' => 'Centro historico demo',
                'observations' => 'Revision anterior de demostracion.',
                'status' => FleetTechnicalInspectionStatus::Expired->value(),
            ]);

            $status = $statuses[$index % count($statuses)];

            FleetTechnicalInspection::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'fleet_equipment_id' => $equipment->id,
                'responsible_user_id' => $responsible->id,
                'reviewed_at' => now()->subMonths(6)->toDateString(),
                'due_at' => match ($status) {
                    FleetTechnicalInspectionStatus::Expired => now()->subDays(10)->toDateString(),
                    FleetTechnicalInspectionStatus::DueSoon => now()->addDays(12)->toDateString(),
                    default => now()->addMonths(4)->toDateString(),
                },
                'result' => $status === FleetTechnicalInspectionStatus::Observed ? 'Observado leve' : 'Aprobado',
                'inspection_center' => 'Centro tecnico demo',
                'observations' => 'Revision tecnica de demostracion.',
                'status' => $status->value(),
            ]);
        });
    }

    /**
     * @param  Collection<int, FleetEquipment>  $equipments
     * @return Collection<int, FleetPreventiveMaintenance>
     */
    protected function seedPreventiveMaintenances(Company $company, $equipments, User $responsible)
    {
        return $equipments->take(3)->map(function (FleetEquipment $equipment, int $index) use ($company, $responsible): FleetPreventiveMaintenance {
            return FleetPreventiveMaintenance::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'fleet_equipment_id' => $equipment->id,
                'responsible_user_id' => $responsible->id,
                'maintenance_type' => ['Cambio de aceite', 'Filtros', 'Inspeccion 500h'][$index],
                'scheduled_date' => now()->addDays($index * 7)->toDateString(),
                'scheduled_odometer' => (float) $equipment->odometer_km + 500,
                'scheduled_hour_meter' => (float) $equipment->hour_meter + 50,
                'priority' => DocumentPriority::Medium->value(),
                'status' => $index === 0
                    ? FleetPreventiveMaintenanceStatus::Scheduled->value()
                    : FleetPreventiveMaintenanceStatus::Completed->value(),
                'cost' => $index === 0 ? null : 850.00,
                'observations' => 'Mantenimiento preventivo demo.',
            ]);
        });
    }

    /**
     * @param  Collection<int, FleetEquipment>  $equipments
     * @return Collection<int, FleetCorrectiveMaintenance>
     */
    protected function seedCorrectiveMaintenances(Company $company, $equipments, User $responsible)
    {
        $broken = $equipments->first(
            fn (FleetEquipment $equipment): bool => $equipment->operational_status === FleetEquipmentOperationalStatus::Broken->value(),
        ) ?? $equipments->last();

        return collect([
            FleetCorrectiveMaintenance::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'fleet_equipment_id' => $broken->id,
                'responsible_user_id' => $responsible->id,
                'failure_at' => now()->subDays(3),
                'failure_description' => 'Falla en motor de arranque.',
                'diagnosis' => 'Motor de arranque desgastado.',
                'supplier_workshop' => 'Taller demo mecanica',
                'estimated_cost' => 1200.00,
                'real_cost' => 980.50,
                'status' => FleetCorrectiveMaintenanceStatus::InRepair->value(),
                'observations' => 'Correctivo demo en curso.',
            ]),
        ]);
    }

    /**
     * @param  Collection<int, FleetEquipment>  $equipments
     * @param  Collection<int, FleetPreventiveMaintenance>  $preventiveRows
     * @param  Collection<int, FleetCorrectiveMaintenance>  $correctiveRows
     * @return Collection<int, FleetWorkOrder>
     */
    protected function seedWorkOrders(
        Company $company,
        Project $project,
        $equipments,
        User $responsible,
        $preventiveRows,
        $correctiveRows,
    ) {
        $definitions = [
            ['OT-0001', FleetWorkOrderType::Preventive, FleetWorkOrderStatus::InProgress, $equipments[0]->id, $preventiveRows->first()?->id, null, 450, 320],
            ['OT-0002', FleetWorkOrderType::Corrective, FleetWorkOrderStatus::Assigned, $equipments[3]->id, null, $correctiveRows->first()?->id, 0, 0],
            ['OT-0003', FleetWorkOrderType::Preventive, FleetWorkOrderStatus::Finished, $equipments[1]->id, $preventiveRows->skip(1)->first()?->id, null, 600, 250],
            ['OT-0004', FleetWorkOrderType::TechnicalInspection, FleetWorkOrderStatus::Generated, $equipments[2]->id, null, null, 0, 0],
        ];

        return collect($definitions)->map(function (array $definition) use ($company, $project, $responsible): FleetWorkOrder {
            [$code, $type, $status, $equipmentId, $preventiveId, $correctiveId, $labor, $spares] = $definition;

            return FleetWorkOrder::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'fleet_equipment_id' => $equipmentId,
                'responsible_user_id' => $responsible->id,
                'code' => $code,
                'type' => $type->value(),
                'issued_at' => now()->subDays(5)->toDateString(),
                'scheduled_date' => now()->addDays(2)->toDateString(),
                'closed_at' => in_array($status, [FleetWorkOrderStatus::Finished, FleetWorkOrderStatus::Closed], true)
                    ? now()->subDay()
                    : null,
                'priority' => DocumentPriority::High->value(),
                'status' => $status->value(),
                'work_description' => 'Orden de trabajo demo '.$code,
                'labor_cost' => $labor,
                'spare_parts_cost' => $spares,
                'total_cost' => $labor + $spares,
                'fleet_preventive_maintenance_id' => $preventiveId,
                'fleet_corrective_maintenance_id' => $correctiveId,
            ]);
        });
    }

    /**
     * @param  Collection<int, FleetWorkOrder>  $workOrders
     */
    protected function seedSpareParts(
        Company $company,
        Project $project,
        ?Supplier $supplier,
        $workOrders,
        User $responsible,
    ): void {
        $parts = collect([
            ['RPT-001', 'Filtro de aceite hidraulico', 'Hidraulico', 24, 5, 45.50],
            ['RPT-002', 'Correa alternador', 'Motor', 3, 4, 120.00],
            ['RPT-003', 'Pastillas de freno', 'Frenos', 1, 6, 89.90],
        ])->map(function (array $definition) use ($company, $project, $supplier): FleetSparePart {
            [$code, $name, $category, $stock, $min, $cost] = $definition;

            return FleetSparePart::withoutGlobalScopes()->create([
                'company_id' => $company->id,
                'supplier_id' => $supplier?->id,
                'warehouse_project_id' => $project->id,
                'code' => $code,
                'name' => $name,
                'category' => $category,
                'unit' => 'und',
                'stock_quantity' => $stock,
                'min_stock' => $min,
                'unit_cost' => $cost,
                'status' => FleetSparePartStatus::Active->value(),
            ]);
        });

        $openWorkOrder = $workOrders->first(
            fn (FleetWorkOrder $order): bool => $order->status === FleetWorkOrderStatus::InProgress->value(),
        );

        FleetSparePartMovement::withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'fleet_spare_part_id' => $parts->first()->id,
            'fleet_work_order_id' => $openWorkOrder?->id,
            'created_by_user_id' => $responsible->id,
            'direction' => FleetSparePartMovementDirection::Outbound->value(),
            'quantity' => 2,
            'unit_cost' => 45.50,
            'total_amount' => 91.00,
            'reference' => 'Consumo demo OT',
        ]);
    }
}
