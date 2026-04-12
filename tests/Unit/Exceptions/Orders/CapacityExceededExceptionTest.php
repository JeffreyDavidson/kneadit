<?php

use App\Exceptions\Orders\CapacityExceededException;
use Carbon\Carbon;

test('stores date and max orders', function () {
    $date = Carbon::parse('2026-04-15');
    $exception = new CapacityExceededException($date, 25);

    expect($exception->date->toDateString())->toBe('2026-04-15')
        ->and($exception->maxOrders)->toBe(25)
        ->and($exception->getMessage())->toBe('Capacity exceeded for 2026-04-15 (max: 25)');
});

test('context returns structured data', function () {
    $date = Carbon::parse('2026-04-15');
    $exception = new CapacityExceededException($date, 25);

    expect($exception->context())->toBe([
        'date' => '2026-04-15',
        'max_orders' => 25,
    ]);
});

test('implements ShouldntReport', function () {
    $exception = new CapacityExceededException(Carbon::now(), 10);

    expect($exception)->toBeInstanceOf(Illuminate\Contracts\Debug\ShouldntReport::class);
});
