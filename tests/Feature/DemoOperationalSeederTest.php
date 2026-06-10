<?php

use App\Enums\AccountsPayableStatus;
use App\Models\AccountsPayable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo operational seeder registers payable payment after uploading required documents', function () {
    $this->seed(DatabaseSeeder::class);

    expect(AccountsPayable::query()->where('status', AccountsPayableStatus::Paid->value())->exists())->toBeTrue();
});
