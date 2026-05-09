<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentObservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentObservation>
 */
class DocumentObservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'document_id' => Document::factory(),
            'user_id' => User::factory(),
            'observation' => fake()->paragraph(),
            'status_after' => 'observado',
        ];
    }
}
