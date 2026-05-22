<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('audit.drivers.database.connection') ?? config('database.default');
        $tableName = config('audit.drivers.database.table', 'audits');
        $schema = Schema::connection($connection);

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $schema->table($tableName, function (Blueprint $table) use ($schema, $tableName) {
            if (! $schema->hasColumn($tableName, 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('tags')->constrained()->nullOnDelete();
            }

            foreach (['user_name', 'active_role', 'module', 'action', 'browser', 'device'] as $column) {
                if (! $schema->hasColumn($tableName, $column)) {
                    $table->string($column)->nullable();
                }
            }

            if (! $schema->hasColumn($tableName, 'observation')) {
                $table->text('observation')->nullable();
            }

            if (! $schema->hasIndex($tableName, ['company_id', 'created_at'])) {
                $table->index(['company_id', 'created_at']);
            }

            if (! $schema->hasIndex($tableName, ['module', 'action'])) {
                $table->index(['module', 'action']);
            }
        });
    }

    public function down(): void
    {
        $connection = config('audit.drivers.database.connection') ?? config('database.default');
        $tableName = config('audit.drivers.database.table', 'audits');
        $schema = Schema::connection($connection);

        if (! $schema->hasTable($tableName)) {
            return;
        }

        $schema->table($tableName, function (Blueprint $table) use ($schema, $tableName) {
            if ($schema->hasIndex($tableName, ['company_id', 'created_at'])) {
                $table->dropIndex(['company_id', 'created_at']);
            }

            if ($schema->hasIndex($tableName, ['module', 'action'])) {
                $table->dropIndex(['module', 'action']);
            }

            if ($schema->hasColumn($tableName, 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }

            $table->dropColumn(array_filter(
                ['user_name', 'active_role', 'module', 'action', 'browser', 'device', 'observation'],
                fn (string $column): bool => $schema->hasColumn($tableName, $column),
            ));
        });
    }
};
