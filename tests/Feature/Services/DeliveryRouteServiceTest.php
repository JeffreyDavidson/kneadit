<?php

use App\Models\Order;
use App\Services\DeliveryRouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it calculates close distance tier for downtown addresses', function () {
    $result = resolve(DeliveryRouteService::class)->calculateDistanceTier('123 Downtown Ave');

    expect($result)
        ->tier->toBe('Close')
        ->color->toBe('green')
        ->estimated_minutes->toBe(10);
});

test('it loads orders with delivery addresses for a date', function () {
    $date = now()->format('Y-m-d');

    Order::factory()->create([
        'delivery_date' => $date,
        'delivery_time' => '14:00',
        'delivery_address' => '123 Main St',
        'total' => 50.00,
    ]);

    // Order without address should be excluded
    Order::factory()->create([
        'delivery_date' => $date,
        'delivery_address' => null,
    ]);

    $result = resolve(DeliveryRouteService::class)->loadOrders($date);

    expect($result)->toHaveCount(1)
        ->first()->delivery_address->toBe('123 Main St');
});
