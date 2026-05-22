<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use App\Models\QuotationComparison;
use App\Models\Requirement;
use App\Models\SupplierQuotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationComparison>
 */
class QuotationComparisonFactory extends Factory
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
            'requirement_id' => Requirement::factory(),
            'selected_supplier_quotation_id' => SupplierQuotation::factory(),
            'selected_by' => User::factory(),
            'compared_at' => now(),
            'selection_reason' => fake()->sentence(),
            'comparison_code' => null,
            'order_code' => strtoupper(fake()->bothify('OC-###')),
            'order_generated_at' => now(),
        ];
    }
}
