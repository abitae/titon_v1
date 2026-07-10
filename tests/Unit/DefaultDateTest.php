<?php

use App\Support\DefaultDate;
use Illuminate\Support\Carbon;

test('today returns current date in Y-m-d format', function () {
    Carbon::setTestNow('2026-07-09 15:30:00');

    expect(DefaultDate::today())->toBe('2026-07-09');
});

test('monthStart returns first day of current month', function () {
    Carbon::setTestNow('2026-07-09 15:30:00');

    expect(DefaultDate::monthStart())->toBe('2026-07-01');
});

test('daysAhead monthsAhead and yearsAhead offset from today', function () {
    Carbon::setTestNow('2026-07-09 15:30:00');

    expect(DefaultDate::daysAhead(7))->toBe('2026-07-16')
        ->and(DefaultDate::monthsAhead(1))->toBe('2026-08-09')
        ->and(DefaultDate::yearsAhead(1))->toBe('2027-07-09');
});

test('filterRange returns month start through today', function () {
    Carbon::setTestNow('2026-07-09 15:30:00');

    expect(DefaultDate::filterRange())->toBe([
        'from' => '2026-07-01',
        'to' => '2026-07-09',
    ]);
});

test('nowDateTimeLocal returns datetime-local format', function () {
    Carbon::setTestNow('2026-07-09 15:30:00');

    expect(DefaultDate::nowDateTimeLocal())->toBe('2026-07-09T15:30');
});
