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
        Schema::table('company_pdf_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('logo_width')->default(32)->after('show_logo');
            $table->unsignedTinyInteger('logo_height')->default(16)->after('logo_width');
            $table->string('logo_position', 12)->default('left')->after('logo_height');
            $table->string('logo_vertical_align', 12)->default('top')->after('logo_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_pdf_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'logo_width',
                'logo_height',
                'logo_position',
                'logo_vertical_align',
            ]);
        });
    }
};
