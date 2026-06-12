<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unique('supplier_quotation_id');
        });

        Schema::table('accounts_payable', function (Blueprint $table): void {
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('accounts_payable', function (Blueprint $table): void {
            $table->dropUnique(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['supplier_quotation_id']);
        });
    }
};
