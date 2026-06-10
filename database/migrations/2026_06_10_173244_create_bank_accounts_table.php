<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_bank_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->string('name');
            $table->string('account_number', 50)->nullable();
            $table->string('currency', 10)->default('PEN');
            $table->decimal('balance', 14, 2)->default(0);
            $table->boolean('is_cash')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'is_cash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
