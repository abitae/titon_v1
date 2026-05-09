<?php

namespace Database\Factories;

use App\Enums\FleetCorrectiveMaintenanceStatus;
use App\Models\Company;
use App\Models\FleetCorrectiveMaintenance;
use App\Models\FleetEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetCorrectiveMaintenance>
 */
class FleetCorrectiveMaintenanceFactory extends Factory
{
    protected $model = FleetCorrectiveMaintenance::class;

    public function definition(): array
    {
        $companyId = Company::factory()->create()->id;

        return [
            'company_id' => $companyId,
            'fleet_equipment_id' => FleetEquipment::factory()->state(['company_id' => $companyId]),
            'responsible_user_id' => null,
            'failure_at' => now(),
            'failure_description' => fake()->sentence(),
            'diagnosis' => fake()->optional()->sentence(),
            'supplier_workshop' => fake()->optional()->company(),
            'estimated_cost' => fake()->randomFloat(2, 100, 5000),
            'real_cost' => null,
            'status' => FleetCorrectiveMaintenanceStatus::Reported->value(),
            'observations' => fake()->optional()->sentence(),
        ];
    }
}
