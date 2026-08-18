<?php

use App\Actions\Orders\GenerateOrderNumber;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it generates an ORD-prefixed order number with a 10-character random suffix', function () {
    $number = (new GenerateOrderNumber)();

    expect($number)
        ->toStartWith('ORD-')
        ->and(strlen($number))->toBe(14)
        ->and($number)->toMatch('/^ORD-[A-Z0-9]{10}$/');
});

test('successive calls produce unique order numbers', function () {
    $numbers = collect(range(1, 5))->map(fn () => (new GenerateOrderNumber)());

    expect($numbers->unique()->count())->toBe(5);
});

test('skips a candidate that already exists in the database', function () {
    $taken = (new GenerateOrderNumber)();
    Order::factory()->create(['order_number' => $taken]);

    $next = (new GenerateOrderNumber)();

    expect($next)->not->toBe($taken);
});
