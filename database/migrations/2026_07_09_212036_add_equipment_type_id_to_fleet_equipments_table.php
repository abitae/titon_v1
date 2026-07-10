<?php

use App\Enums\CatalogType;
use App\Models\CatalogItem;
use App\Models\FleetEquipment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fleet_equipments', function (Blueprint $table): void {
            $table->foreignId('equipment_type_id')
                ->nullable()
                ->after('internal_code')
                ->constrained('catalog_items')
                ->nullOnDelete();
        });

        FleetEquipment::withoutGlobalScopes()
            ->whereNotNull('equipment_type')
            ->where('equipment_type', '!=', '')
            ->orderBy('id')
            ->each(function (FleetEquipment $equipment): void {
                $catalogItem = CatalogItem::withoutGlobalScopes()->firstOrCreate(
                    [
                        'company_id' => $equipment->company_id,
                        'type' => CatalogType::EquipmentType->value(),
                        'name' => $equipment->equipment_type,
                    ],
                    [
                        'code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $equipment->equipment_type) ?: 'TIPO', 0, 6)),
                        'is_active' => true,
                    ],
                );

                $equipment->update(['equipment_type_id' => $catalogItem->id]);
            });
    }

    public function down(): void
    {
        Schema::table('fleet_equipments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('equipment_type_id');
        });
    }
};
