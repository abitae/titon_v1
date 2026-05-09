<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('internal_code');
            $table->string('equipment_type');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('plate')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color')->nullable();
            $table->string('city')->nullable();
            $table->string('operational_status');
            $table->decimal('odometer_km', 12, 2)->nullable();
            $table->decimal('hour_meter', 12, 2)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'internal_code']);
            $table->index(['company_id', 'operational_status']);
            $table->index(['company_id', 'work_project_id']);
        });

        Schema::create('fleet_technical_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('reviewed_at');
            $table->date('due_at');
            $table->string('result');
            $table->string('inspection_center')->nullable();
            $table->text('observations')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'due_at']);
        });

        Schema::create('fleet_preventive_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('maintenance_type');
            $table->date('scheduled_date');
            $table->decimal('scheduled_odometer', 12, 2)->nullable();
            $table->decimal('scheduled_hour_meter', 12, 2)->nullable();
            $table->string('priority');
            $table->string('status');
            $table->decimal('cost', 14, 2)->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'scheduled_date']);
        });

        Schema::create('fleet_corrective_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('failure_at');
            $table->text('failure_description');
            $table->text('diagnosis')->nullable();
            $table->string('supplier_workshop')->nullable();
            $table->decimal('estimated_cost', 14, 2)->nullable();
            $table->decimal('real_cost', 14, 2)->nullable();
            $table->string('status');
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'failure_at']);
        });

        Schema::create('fleet_work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('fleet_equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code');
            $table->string('type');
            $table->date('issued_at');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('priority');
            $table->string('status');
            $table->text('work_description')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('parts_used_description')->nullable();
            $table->decimal('labor_cost', 14, 2)->default(0);
            $table->decimal('spare_parts_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->foreignId('fleet_preventive_maintenance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fleet_corrective_maintenance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fleet_technical_inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });

        Schema::create('fleet_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('warehouse_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit', 32);
            $table->decimal('stock_quantity', 14, 3)->default(0);
            $table->decimal('min_stock', 14, 3)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->string('status');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('fleet_spare_part_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_spare_part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fleet_work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction');
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'fleet_spare_part_id']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_spare_part_movements');
        Schema::dropIfExists('fleet_spare_parts');
        Schema::dropIfExists('fleet_work_orders');
        Schema::dropIfExists('fleet_corrective_maintenances');
        Schema::dropIfExists('fleet_preventive_maintenances');
        Schema::dropIfExists('fleet_technical_inspections');
        Schema::dropIfExists('fleet_equipments');
    }
};
