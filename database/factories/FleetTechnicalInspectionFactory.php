<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\FleetTechnicalInspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetTechnicalInspection>
 */
class FleetTechnicalInspectionFactory extends Factory
{
    protected $model = FleetTechnicalInspection::class;

    public function definition(): array
    {
        $companyId = Company::factory()->create()->id;

        return [
            'company_id' => $companyId,
            'fleet_equipment_id' => FleetEquipment::factory()->state(['company_id' => $companyId]),
            'responsible_user_id' => null,
            'reviewed_at' => fake()->date(),
            'due_at' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'result' => fake()->randomElement(['Aprobado', 'Condicional']),
            'inspection_center' => fake()->company(),
            'observations' => fake()->optional()->sentence(),
            'status' => 'vigente',
        ];
    }

    public function forEquipment(FleetEquipment $equipment): static
    {
        return $this->state(fn (array $attrs): array => [
            'company_id' => $equipment->company_id,
            'fleet_equipment_id' => $equipment->id,
        ]);
    }
}
