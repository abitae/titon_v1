<?php

use App\Enums\AccountsPayableStatus;
use App\Enums\OrderStatus;
use App\Models\AccountsPayable;
use App\Models\CostType;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Requirement;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo operational seeder registers payable payment after uploading required documents', function () {
    $this->seed(DatabaseSeeder::class);

    expect(AccountsPayable::query()->where('status', AccountsPayableStatus::Paid->value())->exists())->toBeTrue();
});

test('database seeder fills operational records with complete fields', function () {
    $this->seed(DatabaseSeeder::class);

    expect(CostType::query()->where('code', 'MAT')->exists())->toBeTrue()
        ->and(Project::query()->whereNotNull('client_name')->whereNotNull('estimated_budget')->exists())->toBeTrue()
        ->and(Supplier::query()->whereNotNull('cci')->whereNotNull('bank_account')->whereNotNull('address')->exists())->toBeTrue()
        ->and(Requirement::query()->whereNotNull('cost_type_id')->whereNotNull('needed_date')->whereNotNull('observation')->exists())->toBeTrue()
        ->and(SupplierQuotation::query()->whereNotNull('observation')->whereNotNull('warranty')->whereNotNull('valid_until')->exists())->toBeTrue();

    $order = PurchaseOrder::query()
        ->whereNotNull('observation')
        ->whereNotNull('conditions')
        ->whereNotNull('approved_by')
        ->first();

    expect($order)->not->toBeNull()
        ->and($order?->items()->whereNotNull('observation')->exists())->toBeTrue();

    expect(PurchaseOrder::query()->where('status', OrderStatus::Cancelled->value())->whereNotNull('cancellation_reason')->exists())->toBeTrue()
        ->and(Requirement::query()->where('title', 'Alquiler de compactadora demo')->exists())->toBeTrue()
        ->and(SupplierQuotation::query()->where('quotation_number', 'like', 'COT-ALQ-%')->exists())->toBeTrue();
});
