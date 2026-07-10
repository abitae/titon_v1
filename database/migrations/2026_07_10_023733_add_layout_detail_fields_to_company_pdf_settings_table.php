<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_pdf_settings', function (Blueprint $table): void {
            $table->string('header_text_align', 12)->default('left')->after('header_layout');
            $table->unsignedTinyInteger('header_padding')->default(8)->after('header_text_align');
            $table->unsignedTinyInteger('title_font_size')->default(13)->after('header_padding');
            $table->unsignedTinyInteger('meta_font_size')->default(9)->after('title_font_size');
            $table->boolean('show_header_rule')->default(true)->after('meta_font_size');
            $table->unsignedTinyInteger('header_rule_thickness')->default(2)->after('show_header_rule');
            $table->boolean('show_footer_border')->default(true)->after('footer_text');
            $table->unsignedTinyInteger('footer_font_size')->default(9)->after('show_footer_border');
        });
    }

    public function down(): void
    {
        Schema::table('company_pdf_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'header_text_align',
                'header_padding',
                'title_font_size',
                'meta_font_size',
                'show_header_rule',
                'header_rule_thickness',
                'show_footer_border',
                'footer_font_size',
            ]);
        });
    }
};
