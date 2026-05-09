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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 50);
            $table->foreignId('document_type_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('origin_area_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('destination_area_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignId('current_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50);
            $table->string('priority', 50)->default('media');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'current_user_id']);
            $table->index(['company_id', 'work_project_id']);
            $table->index(['company_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
