<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_pdf_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('show_logo')->default(true);
            $table->string('header_layout', 24)->default('classic');
            $table->boolean('show_company_name')->default(true);
            $table->boolean('show_business_name')->default(true);
            $table->boolean('show_ruc')->default(true);
            $table->boolean('show_address')->default(true);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_email')->default(false);
            $table->string('primary_color', 16)->nullable();
            $table->string('secondary_color', 16)->nullable();
            $table->string('footer_text', 500)->nullable();
            $table->unsignedTinyInteger('margin_top')->default(32);
            $table->unsignedTinyInteger('margin_bottom')->default(16);
            $table->unsignedTinyInteger('margin_left')->default(12);
            $table->unsignedTinyInteger('margin_right')->default(12);
            $table->boolean('show_page_numbers')->default(true);
            $table->boolean('show_generated_at')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_pdf_settings');
    }
};
