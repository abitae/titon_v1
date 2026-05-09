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
        if (! Schema::hasColumn('companies', 'correlative_prefix')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('correlative_prefix', 32)->nullable()->after('name');
            });
        }

        if (! Schema::hasTable('company_correlative_formats')) {
            Schema::create('company_correlative_formats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('subject', 64);
                $table->string('series', 16)->default('');
                $table->string('suffix', 24);
                $table->string('template');
                $table->unsignedTinyInteger('pad_length')->default(6);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'subject', 'series'], 'cc_fmt_co_subj_series_uq');
                $table->index(['company_id', 'subject']);
            });
        }

        if (! Schema::hasTable('company_correlative_sequences')) {
            Schema::create('company_correlative_sequences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('subject', 64);
                $table->string('series', 16)->default('');
                $table->unsignedSmallInteger('year');
                $table->unsignedBigInteger('last_number')->default(0);
                $table->timestamps();

                $table->unique(['company_id', 'subject', 'series', 'year'], 'cc_seq_co_subj_series_yr_uq');
                $table->index(['company_id', 'year']);
            });
        }

        if (! Schema::hasColumn('quotation_comparisons', 'comparison_code')) {
            Schema::table('quotation_comparisons', function (Blueprint $table) {
                $table->string('comparison_code', 80)->nullable()->after('purchase_request_id');
            });
        }

        if (! Schema::hasColumn('contract_payment_schedules', 'registry_code')) {
            Schema::table('contract_payment_schedules', function (Blueprint $table) {
                $table->string('registry_code', 80)->nullable()->after('supplier_contract_id');
            });
        }

        if (! Schema::hasColumn('supplier_payments', 'registry_code')) {
            Schema::table('supplier_payments', function (Blueprint $table) {
                $table->string('registry_code', 80)->nullable()->after('supplier_contract_id');
            });
        }

        if (Schema::hasTable('fleet_technical_inspections') && ! Schema::hasColumn('fleet_technical_inspections', 'code')) {
            Schema::table('fleet_technical_inspections', function (Blueprint $table) {
                $table->string('code', 80)->nullable()->after('fleet_equipment_id');
            });
        }

        if (Schema::hasTable('fleet_preventive_maintenances') && ! Schema::hasColumn('fleet_preventive_maintenances', 'code')) {
            Schema::table('fleet_preventive_maintenances', function (Blueprint $table) {
                $table->string('code', 80)->nullable()->after('fleet_equipment_id');
            });
        }

        if (Schema::hasTable('fleet_corrective_maintenances') && ! Schema::hasColumn('fleet_corrective_maintenances', 'code')) {
            Schema::table('fleet_corrective_maintenances', function (Blueprint $table) {
                $table->string('code', 80)->nullable()->after('fleet_equipment_id');
            });
        }

        if (Schema::hasTable('fleet_spare_part_movements') && ! Schema::hasColumn('fleet_spare_part_movements', 'movement_code')) {
            Schema::table('fleet_spare_part_movements', function (Blueprint $table) {
                $table->string('movement_code', 80)->nullable()->after('fleet_spare_part_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_spare_part_movements', function (Blueprint $table) {
            $table->dropColumn('movement_code');
        });

        Schema::table('fleet_corrective_maintenances', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('fleet_preventive_maintenances', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('fleet_technical_inspections', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropColumn('registry_code');
        });

        Schema::table('contract_payment_schedules', function (Blueprint $table) {
            $table->dropColumn('registry_code');
        });

        Schema::table('quotation_comparisons', function (Blueprint $table) {
            $table->dropColumn('comparison_code');
        });

        Schema::dropIfExists('company_correlative_sequences');
        Schema::dropIfExists('company_correlative_formats');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('correlative_prefix');
        });
    }
};
