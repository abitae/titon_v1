<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('item_type');
            $table->string('description');
            $table->string('unit', 32);
            $table->decimal('stock_quantity', 14, 3)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->string('status')->default('activo');
            $table->timestamps();

            $table->unique(
                ['company_id', 'work_project_id', 'item_type', 'description', 'unit'],
                'warehouse_stock_items_unique_key',
            );
            $table->index(['company_id', 'work_project_id']);
            $table->index(['company_id', 'item_type']);
        });

        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('transfer_code');
            $table->foreignId('source_work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('destination_work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('warehouse_stock_item_id')->constrained('warehouse_stock_items')->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('transfer_date');
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 4);
            $table->decimal('total_amount', 14, 2);
            $table->text('reference')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'transfer_code']);
            $table->index(['company_id', 'transfer_date']);
        });

        Schema::create('warehouse_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_stock_item_id')->constrained('warehouse_stock_items')->cascadeOnDelete();
            $table->foreignId('warehouse_transfer_id')->nullable()->constrained('warehouse_transfers')->nullOnDelete();
            $table->string('movement_code');
            $table->string('direction');
            $table->string('source');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('order_conformity_id')->nullable()->constrained('order_conformities')->nullOnDelete();
            $table->foreignId('responsible_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('movement_date');
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 4);
            $table->decimal('total_amount', 14, 2);
            $table->text('reference')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'movement_code']);
            $table->unique(['order_item_id', 'source'], 'warehouse_movements_order_item_source_unique');
            $table->index(['company_id', 'movement_date']);
            $table->index(['warehouse_stock_item_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_movements');
        Schema::dropIfExists('warehouse_transfers');
        Schema::dropIfExists('warehouse_stock_items');
    }
};
