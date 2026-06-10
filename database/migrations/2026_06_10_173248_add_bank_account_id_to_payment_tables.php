<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts_payable_payments', function (Blueprint $table): void {
            $table->foreignId('bank_account_id')->nullable()->after('bank_id')->constrained()->nullOnDelete();
        });

        Schema::table('supplier_payments', function (Blueprint $table): void {
            $table->foreignId('bank_account_id')->nullable()->after('bank_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accounts_payable_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bank_account_id');
        });

        Schema::table('supplier_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('bank_account_id');
        });
    }
};
