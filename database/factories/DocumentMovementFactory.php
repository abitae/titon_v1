<?php

namespace Database\Factories;

use App\Enums\DocumentMovementType;
use App\Enums\DocumentStatus;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentMovement>
 */
class DocumentMovementFactory extends Factory
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
            'from_area_id' => CatalogItem::factory(),
            'to_area_id' => CatalogItem::factory(),
            'from_user_id' => User::factory(),
            'to_user_id' => User::factory(),
            'action' => fake()->randomElement(array_map(fn (DocumentMovementType $type): string => $type->value(), DocumentMovementType::cases())),
            'from_status' => fake()->randomElement(DocumentStatus::values()),
            'to_status' => fake()->randomElement(DocumentStatus::values()),
            'notes' => fake()->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
