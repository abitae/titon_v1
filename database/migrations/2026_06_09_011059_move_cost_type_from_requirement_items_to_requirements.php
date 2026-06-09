<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table): void {
            $table->foreignId('cost_type_id')->nullable()->after('requirement_type')->constrained('cost_types')->nullOnDelete();
        });

        if (Schema::hasColumn('requirement_items', 'cost_type_id')) {
            DB::table('requirement_items')
                ->whereNotNull('cost_type_id')
                ->orderBy('id')
                ->get(['requirement_id', 'cost_type_id'])
                ->groupBy('requirement_id')
                ->each(function ($items, $requirementId): void {
                    DB::table('requirements')
                        ->where('id', $requirementId)
                        ->whereNull('cost_type_id')
                        ->update(['cost_type_id' => $items->first()->cost_type_id]);
                });

            Schema::table('requirement_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('cost_type_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_items', function (Blueprint $table): void {
            $table->foreignId('cost_type_id')->nullable()->after('item_type')->constrained('cost_types')->nullOnDelete();
        });

        DB::table('requirements')
            ->whereNotNull('cost_type_id')
            ->orderBy('id')
            ->get(['id', 'cost_type_id'])
            ->each(function (object $requirement): void {
                DB::table('requirement_items')
                    ->where('requirement_id', $requirement->id)
                    ->update(['cost_type_id' => $requirement->cost_type_id]);
            });

        Schema::table('requirements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cost_type_id');
        });
    }
};
