<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('purchase_requests', 'requirements');
        Schema::rename('purchase_request_items', 'requirement_items');
        Schema::rename('purchase_orders', 'orders');
        Schema::rename('purchase_order_items', 'order_items');

        Schema::table('requirements', function (Blueprint $table): void {
            $table->foreignId('responsible_user_id')->nullable()->after('work_project_id')->constrained('users')->nullOnDelete();
            $table->string('title')->nullable()->after('code');
            $table->string('requirement_type', 50)->default('material')->after('title');
            $table->string('requested_by_name', 150)->nullable()->after('requirement_type');
            $table->date('needed_date')->nullable()->after('request_date');
            $table->text('observation')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->softDeletes();
            $table->index(['company_id', 'responsible_user_id']);
        });

        Schema::table('requirement_items', function (Blueprint $table): void {
            $table->renameColumn('purchase_request_id', 'requirement_id');
            $table->string('item_type', 50)->default('material')->after('requirement_id');
            $table->renameColumn('product_or_service', 'description');
            $table->decimal('estimated_unit_price', 14, 2)->nullable()->after('technical_specification');
            $table->decimal('estimated_total', 14, 2)->nullable()->after('estimated_unit_price');
        });

        Schema::table('supplier_quotations', function (Blueprint $table): void {
            $table->renameColumn('purchase_request_id', 'requirement_id');
            $table->string('quotation_number', 80)->nullable()->after('code');
            $table->string('status', 50)->default('registrada')->after('observation');
            $table->decimal('total_score', 8, 2)->nullable()->after('status');
            $table->unsignedInteger('delivery_time_days')->nullable()->after('total_score');
        });

        DB::table('supplier_quotations')->whereNotNull('delivery_time')->update([
            'delivery_time_days' => DB::raw('delivery_time'),
        ]);

        Schema::table('supplier_quotations', function (Blueprint $table): void {
            $table->dropColumn('delivery_time');
        });

        Schema::table('supplier_quotation_items', function (Blueprint $table): void {
            $table->foreignId('requirement_item_id')->nullable()->after('supplier_quotation_id')->constrained('requirement_items')->nullOnDelete();
            $table->text('observation')->nullable();
        });

        Schema::table('quotation_comparisons', function (Blueprint $table): void {
            $table->renameColumn('purchase_request_id', 'requirement_id');
            $table->renameColumn('purchase_order_code', 'order_code');
            $table->renameColumn('purchase_order_generated_at', 'order_generated_at');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('requirement_id')->nullable()->after('work_project_id')->constrained('requirements')->nullOnDelete();
            $table->string('order_type', 20)->default('compra')->after('code');
            $table->index(['company_id', 'requirement_id']);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->renameColumn('purchase_order_id', 'order_id');
            $table->renameColumn('product_or_service', 'description');
            $table->text('observation')->nullable();
        });

        if (Schema::hasColumn('supplier_contracts', 'purchase_order_id')) {
            Schema::table('supplier_contracts', function (Blueprint $table): void {
                $table->renameColumn('purchase_order_id', 'order_id');
            });
        }

        $this->mapRequirementStatuses();
        $this->mapOrderStatuses();

        Schema::create('requirement_supplier_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sent_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('sent_at');
            $table->string('status', 50)->default('pendiente');
            $table->text('message')->nullable();
            $table->date('response_deadline')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'requirement_id', 'supplier_id'], 'req_supplier_invitation_unique');
            $table->index(['company_id', 'requirement_id']);
        });

        Schema::create('quotation_score_parameters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_score', 8, 2)->default(10);
            $table->decimal('weight', 8, 2)->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'active']);
        });

        Schema::create('quotation_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_score_parameter_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('weighted_score', 8, 2)->default(0);
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['supplier_quotation_id', 'quotation_score_parameter_id'], 'quotation_score_unique');
        });

        Schema::create('order_conformities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('responsible_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('conformity_date');
            $table->string('result', 50);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'order_id']);
        });

        Schema::create('accounts_payable', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('code', 50);
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('currency', 10)->default('PEN');
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('status', 50)->default('pendiente_documentos');
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['company_id', 'work_project_id']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('payable_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accounts_payable_id')->constrained('accounts_payable')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->boolean('required')->default(true);
            $table->boolean('uploaded')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->string('status', 50)->default('pendiente');
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['accounts_payable_id', 'document_type']);
        });

        Schema::create('accounts_payable_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accounts_payable_id')->constrained('accounts_payable')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('PEN');
            $table->foreignId('payment_method_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('operation_type_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->string('operation_number', 100)->nullable();
            $table->foreignId('paid_by')->constrained('users')->cascadeOnDelete();
            $table->string('concept');
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'accounts_payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_payable_payments');
        Schema::dropIfExists('payable_documents');
        Schema::dropIfExists('accounts_payable');
        Schema::dropIfExists('order_conformities');
        Schema::dropIfExists('quotation_scores');
        Schema::dropIfExists('quotation_score_parameters');
        Schema::dropIfExists('requirement_supplier_invitations');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('requirement_id');
            $table->dropColumn('order_type');
        });

        Schema::rename('orders', 'purchase_orders');
        Schema::rename('order_items', 'purchase_order_items');
        Schema::rename('requirements', 'purchase_requests');
        Schema::rename('requirement_items', 'purchase_request_items');
    }

    protected function mapRequirementStatuses(): void
    {
        $map = [
            'solicitada' => 'creado',
            'en_cotizacion' => 'en_proceso',
            'cotizada' => 'en_proceso',
            'en_evaluacion' => 'en_proceso',
            'adjudicada' => 'en_proceso',
            'orden_generada' => 'atendido',
            'cerrada' => 'atendido',
        ];

        foreach ($map as $from => $to) {
            DB::table('requirements')->where('status', $from)->update(['status' => $to]);
        }
    }

    protected function mapOrderStatuses(): void
    {
        $map = [
            'generada' => 'emitida',
            'en_aprobacion' => 'enviada',
            'aprobada' => 'en_atencion',
            'observada' => 'en_atencion',
            'convertida_a_contrato' => 'atendida',
            'cerrada' => 'conforme',
        ];

        foreach ($map as $from => $to) {
            DB::table('orders')->where('status', $from)->update(['status' => $to]);
        }
    }
};
