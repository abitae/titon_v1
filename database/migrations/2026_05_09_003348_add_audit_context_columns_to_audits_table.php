<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('tags')->constrained()->nullOnDelete();
            $table->string('user_name')->nullable()->after('company_id');
            $table->string('active_role')->nullable()->after('user_name');
            $table->string('module')->nullable()->after('active_role');
            $table->string('action')->nullable()->after('module');
            $table->string('browser')->nullable()->after('action');
            $table->string('device')->nullable()->after('browser');
            $table->text('observation')->nullable()->after('device');

            $table->index(['company_id', 'created_at']);
            $table->index(['module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['module', 'action']);
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn([
                'user_name',
                'active_role',
                'module',
                'action',
                'browser',
                'device',
                'observation',
            ]);
        });
    }
};
