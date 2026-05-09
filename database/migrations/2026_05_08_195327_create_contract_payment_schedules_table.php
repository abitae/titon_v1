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
        Schema::create('contract_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('installment_number');
            $table->string('description');
            $table->date('due_date');
            $table->decimal('scheduled_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('status', 50)->default('pendiente');
            $table->timestamps();

            $table->unique(
                ['supplier_contract_id', 'installment_number'],
                'contract_pay_sched_contract_install_nr_uq'
            );
            $table->index(['company_id', 'supplier_contract_id']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payment_schedules');
    }
};
