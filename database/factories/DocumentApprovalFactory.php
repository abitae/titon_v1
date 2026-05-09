<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentApproval>
 */
class DocumentApprovalFactory extends Factory
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
            'decision' => fake()->randomElement(['approved', 'rejected']),
            'comments' => fake()->sentence(),
            'resolved_at' => now(),
        ];
    }
}
