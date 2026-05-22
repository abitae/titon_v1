<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = $this->auditConnection();
        $tableName = $this->auditTable();

        Schema::connection($connection)->create($tableName, function (Blueprint $table) {
            $morphPrefix = config('audit.user.morph_prefix', 'user');

            $table->bigIncrements('id');
            $table->string($morphPrefix.'_type')->nullable();
            $table->unsignedBigInteger($morphPrefix.'_id')->nullable();
            $table->string('event');
            $table->nullableMorphs('auditable');
            $table->longText('old_values')->nullable();
            $table->longText('new_values')->nullable();
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();
            $table->string('tags')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('active_role')->nullable();
            $table->string('module')->nullable();
            $table->string('action')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index([$morphPrefix.'_id', $morphPrefix.'_type']);
            $table->index(['company_id', 'created_at']);
            $table->index(['module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->auditConnection())->dropIfExists($this->auditTable());
    }

    protected function auditConnection(): string
    {
        return config('audit.drivers.database.connection') ?? config('database.default');
    }

    protected function auditTable(): string
    {
        return config('audit.drivers.database.table', 'audits');
    }
};
