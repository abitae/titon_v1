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
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('supplier_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_payment_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 10)->default('PEN');
            $table->foreignId('operation_type_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->string('operation_number', 100)->nullable();
            $table->foreignId('responsible_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('concept');
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'supplier_contract_id']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['company_id', 'work_project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
