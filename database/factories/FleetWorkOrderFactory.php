<?php

namespace Database\Factories;

use App\Enums\DocumentPriority;
use App\Enums\FleetWorkOrderStatus;
use App\Enums\FleetWorkOrderType;
use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\FleetWorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetWorkOrder>
 */
class FleetWorkOrderFactory extends Factory
{
    protected $model = FleetWorkOrder::class;

    public function definition(): array
    {
        $companyId = Company::factory()->create()->id;

        return [
            'company_id' => $companyId,
            'work_project_id' => null,
            'fleet_equipment_id' => FleetEquipment::factory()->state(['company_id' => $companyId]),
            'responsible_user_id' => null,
            'code' => 'OT-'.fake()->unique()->numerify('######'),
            'type' => FleetWorkOrderType::Preventive->value(),
            'issued_at' => now()->toDateString(),
            'scheduled_date' => fake()->optional()->date(),
            'closed_at' => null,
            'priority' => DocumentPriority::Medium->value(),
            'status' => FleetWorkOrderStatus::Generated->value(),
            'work_description' => fake()->sentence(),
            'diagnosis' => null,
            'parts_used_description' => null,
            'labor_cost' => 0,
            'spare_parts_cost' => 0,
            'total_cost' => 0,
            'fleet_preventive_maintenance_id' => null,
            'fleet_corrective_maintenance_id' => null,
            'fleet_technical_inspection_id' => null,
        ];
    }
}
