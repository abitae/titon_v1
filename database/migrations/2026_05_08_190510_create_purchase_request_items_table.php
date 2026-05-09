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
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->string('product_or_service');
            $table->string('unit', 50);
            $table->decimal('quantity', 14, 2);
            $table->text('technical_specification')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'purchase_request_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
