<?php

namespace Database\Factories;

use App\Enums\FleetEquipmentOperationalStatus;
use App\Models\Company;
use App\Models\FleetEquipment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FleetEquipment>
 */
class FleetEquipmentFactory extends Factory
{
    protected $model = FleetEquipment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'work_project_id' => null,
            'responsible_user_id' => null,
            'internal_code' => 'EQ-'.fake()->unique()->numerify('####'),
            'equipment_type' => fake()->randomElement(['Retroexcavadora', 'Camión', 'Generador', 'Andamio']),
            'name' => fake()->words(3, true),
            'brand' => fake()->company(),
            'model' => fake()->bothify('M-###'),
            'serial_number' => strtoupper(fake()->bothify('SN-???####')),
            'plate' => fake()->optional()->numerify('A##-???'),
            'year' => (int) fake()->year(),
            'color' => fake()->safeColorName(),
            'city' => fake()->city(),
            'operational_status' => FleetEquipmentOperationalStatus::Operational->value(),
            'odometer_km' => fake()->randomFloat(2, 0, 250000),
            'hour_meter' => fake()->randomFloat(2, 0, 12000),
            'acquisition_date' => fake()->date(),
            'observations' => fake()->optional()->sentence(),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attrs): array => [
            'company_id' => $company->id,
        ]);
    }

    public function forProject(Project $project): static
    {
        return $this->state(fn (array $attrs): array => [
            'company_id' => $project->company_id,
            'work_project_id' => $project->id,
        ]);
    }

    public function withResponsible(?User $user = null): static
    {
        return $this->state(fn (array $attrs): array => [
            'responsible_user_id' => $user?->id,
        ]);
    }
}
