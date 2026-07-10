<?php

use App\Models\FleetSparePart;
use App\Support\Decimal;

test('decimal compare returns expected ordering', function (string|float $left, string|float $right, int $expected): void {
    expect(Decimal::compare($left, $right, 3))->toBe($expected);
})->with([
    'less than' => ['4.500', '5.000', -1],
    'equal' => ['5.000', '5', 0],
    'greater than' => ['6.250', '6.000', 1],
]);

test('fleet spare part detects stock at or below minimum', function (): void {
    $part = FleetSparePart::factory()->make([
        'stock_quantity' => '3.000',
        'min_stock' => '5.000',
    ]);

    expect($part->isBelowMinStock())->toBeTrue();

    $part->stock_quantity = '5.000';

    expect($part->isBelowMinStock())->toBeTrue();

    $part->stock_quantity = '5.001';

    expect($part->isBelowMinStock())->toBeFalse();
});
