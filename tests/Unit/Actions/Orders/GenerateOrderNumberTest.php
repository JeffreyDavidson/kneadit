<?php

use App\Actions\Orders\GenerateOrderNumber;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it generates the first order number', function () {
    $number = (new GenerateOrderNumber)();

    expect($number)->toBe('ORD-000001');
});

test('it increments from the highest existing order number', function () {
    Order::factory()->create(['order_number' => 'ORD-000042']);

    $number = (new GenerateOrderNumber)();

    expect($number)->toBe('ORD-000043');
});
