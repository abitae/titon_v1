<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_pdf_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('company_pdf_settings', 'logo_width')) {
                $table->unsignedTinyInteger('logo_width')->default(32)->after('show_logo');
            }

            if (! Schema::hasColumn('company_pdf_settings', 'logo_height')) {
                $table->unsignedTinyInteger('logo_height')->default(16)->after('logo_width');
            }

            if (! Schema::hasColumn('company_pdf_settings', 'logo_position')) {
                $table->string('logo_position', 12)->default('left')->after('logo_height');
            }

            if (! Schema::hasColumn('company_pdf_settings', 'logo_vertical_align')) {
                $table->string('logo_vertical_align', 12)->default('top')->after('logo_position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_pdf_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('company_pdf_settings', 'logo_vertical_align')) {
                $table->dropColumn('logo_vertical_align');
            }

            if (Schema::hasColumn('company_pdf_settings', 'logo_position')) {
                $table->dropColumn('logo_position');
            }

            if (Schema::hasColumn('company_pdf_settings', 'logo_height')) {
                $table->dropColumn('logo_height');
            }

            if (Schema::hasColumn('company_pdf_settings', 'logo_width')) {
                $table->dropColumn('logo_width');
            }
        });
    }
};
