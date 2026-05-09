<?php

namespace Database\Factories;

use App\Enums\DocumentPriority;
use App\Enums\FleetPreventiveMaintenanceStatus;
use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\FleetPreventiveMaintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetPreventiveMaintenance>
 */
class FleetPreventiveMaintenanceFactory extends Factory
{
    protected $model = FleetPreventiveMaintenance::class;

    public function definition(): array
    {
        $companyId = Company::factory()->create()->id;

        return [
            'company_id' => $companyId,
            'fleet_equipment_id' => FleetEquipment::factory()->state(['company_id' => $companyId]),
            'responsible_user_id' => null,
            'maintenance_type' => fake()->randomElement(['Aceite', 'Filtros', 'Inspección general']),
            'scheduled_date' => fake()->date(),
            'scheduled_odometer' => fake()->optional()->randomFloat(2, 0, 90000),
            'scheduled_hour_meter' => fake()->optional()->randomFloat(2, 0, 6000),
            'priority' => DocumentPriority::Medium->value(),
            'status' => FleetPreventiveMaintenanceStatus::Scheduled->value(),
            'cost' => null,
            'observations' => fake()->optional()->sentence(),
        ];
    }
}
