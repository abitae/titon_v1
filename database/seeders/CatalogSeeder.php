<?php

namespace Database\Seeders;

use App\Enums\CatalogType;
use App\Models\CatalogItem;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalogs = [
            CatalogType::Bank->value() => [
                ['name' => 'BCP', 'code' => 'BCP'],
                ['name' => 'BBVA', 'code' => 'BBVA'],
                ['name' => 'Interbank', 'code' => 'IBK'],
            ],
            CatalogType::PaymentMethod->value() => [
                ['name' => 'Transferencia', 'code' => 'TRF'],
                ['name' => 'Depósito', 'code' => 'DEP'],
                ['name' => 'Cheque', 'code' => 'CHQ'],
                ['name' => 'Efectivo', 'code' => 'EFE'],
            ],
            CatalogType::OperationType->value() => [
                ['name' => 'Transferencia interbancaria', 'code' => 'TBI'],
                ['name' => 'Abono en cuenta', 'code' => 'ABN'],
            ],
            CatalogType::City->value() => [
                ['name' => 'Lima', 'code' => 'LIM'],
                ['name' => 'Arequipa', 'code' => 'AQP'],
                ['name' => 'Trujillo', 'code' => 'TRU'],
            ],
            CatalogType::EquipmentType->value() => [
                ['name' => 'Retroexcavadora', 'code' => 'RETRO'],
                ['name' => 'Camion', 'code' => 'CAM'],
                ['name' => 'Generador', 'code' => 'GEN'],
                ['name' => 'Equipo menor', 'code' => 'MENOR'],
                ['name' => 'Compactacion', 'code' => 'COMP'],
            ],
        ];

        Company::query()->each(function (Company $company) use ($catalogs): void {
            foreach ($catalogs as $type => $items) {
                foreach ($items as $item) {
                    CatalogItem::withoutGlobalScopes()->firstOrCreate(
                        [
                            'company_id' => $company->id,
                            'type' => $type,
                            'name' => $item['name'],
                        ],
                        [
                            'code' => $item['code'],
                            'is_active' => true,
                        ],
                    );
                }
            }
        });
    }
}
