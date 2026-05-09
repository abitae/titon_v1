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
        Schema::create('quotation_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('selected_supplier_quotation_id')->nullable()->constrained('supplier_quotations')->nullOnDelete();
            $table->foreignId('selected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('compared_at')->nullable();
            $table->text('selection_reason')->nullable();
            $table->string('purchase_order_code', 50)->nullable();
            $table->timestamp('purchase_order_generated_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'purchase_request_id']);
            $table->index(['company_id', 'work_project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_comparisons');
    }
};
