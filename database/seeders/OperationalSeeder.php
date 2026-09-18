<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OperationalSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CostTypeSeeder::class,
            ProjectSeeder::class,
            SupplierSeeder::class,
            RequirementSeeder::class,
            QuotationSeeder::class,
            PurchaseOrderSeeder::class,
        ]);
    }
}
