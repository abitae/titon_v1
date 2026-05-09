<?php

namespace Database\Factories;

use App\Enums\CatalogType;
use App\Enums\DocumentPriority;
use App\Enums\DocumentStatus;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
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
            'work_project_id' => Project::factory(),
            'created_by_user_id' => User::factory(),
            'code' => strtoupper(fake()->bothify('DOC-###')),
            'document_number' => fake()->numerify('DOC-####'),
            'document_type_id' => CatalogItem::factory()->state([
                'type' => CatalogType::DocumentType->value(),
            ]),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'origin_area_id' => CatalogItem::factory()->state([
                'type' => CatalogType::Area->value(),
            ]),
            'destination_area_id' => CatalogItem::factory()->state([
                'type' => CatalogType::Area->value(),
            ]),
            'current_user_id' => User::factory(),
            'status' => fake()->randomElement(DocumentStatus::values()),
            'priority' => fake()->randomElement(DocumentPriority::values()),
            'issue_date' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'reception_date' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'observations' => fake()->sentence(),
        ];
    }
}
