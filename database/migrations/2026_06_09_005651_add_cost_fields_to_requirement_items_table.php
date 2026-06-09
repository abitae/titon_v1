<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->foreignId('cost_type_id')->nullable()->after('item_type')->constrained('cost_types')->nullOnDelete();
            $table->string('cost_center_ua', 150)->nullable()->after('cost_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requirement_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cost_type_id');
            $table->dropColumn('cost_center_ua');
        });
    }
};
