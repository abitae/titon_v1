<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->string('movement_code', 50);
            $table->string('direction', 20);
            $table->string('type', 40);
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('PEN');
            $table->decimal('balance_after', 14, 2);
            $table->date('movement_date');
            $table->string('concept');
            $table->string('reference')->nullable();
            $table->foreignId('payment_method_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('operation_type_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->string('operation_number', 100)->nullable();
            $table->nullableMorphs('source');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'movement_date']);
            $table->index(['bank_account_id', 'movement_date']);
            $table->unique(['company_id', 'movement_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_movements');
    }
};
