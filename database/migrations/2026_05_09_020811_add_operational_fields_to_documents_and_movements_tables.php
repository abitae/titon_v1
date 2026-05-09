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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_number', 100)->nullable()->after('code');
            $table->date('reception_date')->nullable()->after('issue_date');
            $table->text('observations')->nullable()->after('due_date');
            $table->timestamp('attended_at')->nullable()->after('observations');
            $table->timestamp('archived_at')->nullable()->after('attended_at');
            $table->timestamp('cancelled_at')->nullable()->after('archived_at');
            $table->text('annulment_reason')->nullable()->after('cancelled_at');

            $table->index(['company_id', 'document_number']);
            $table->index(['company_id', 'reception_date']);
        });

        Schema::table('document_movements', function (Blueprint $table) {
            $table->index(['company_id', 'to_status', 'created_at'], 'document_movements_status_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_movements', function (Blueprint $table) {
            $table->dropIndex('document_movements_status_created_index');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'document_number']);
            $table->dropIndex(['company_id', 'reception_date']);
            $table->dropColumn([
                'document_number',
                'reception_date',
                'observations',
                'attended_at',
                'archived_at',
                'cancelled_at',
                'annulment_reason',
            ]);
        });
    }
};
