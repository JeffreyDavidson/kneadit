<?php

use App\Models\Orders\Order;
use App\Services\Delivery\DeliveryRouteService;
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

test('it calculates close distance tier for center addresses', function () {
    $result = resolve(DeliveryRouteService::class)->calculateDistanceTier('55 Center Blvd');

    expect($result)
        ->tier->toBe('Close')
        ->color->toBe('green')
        ->estimated_minutes->toBe(10);
});

test('it calculates medium distance tier for directional addresses', function (string $address) {
    $result = resolve(DeliveryRouteService::class)->calculateDistanceTier($address);

    expect($result)
        ->tier->toBe('Medium')
        ->color->toBe('yellow')
        ->estimated_minutes->toBe(20);
})->with([
    '500 West Oak Dr',
    '100 East Elm St',
    '42 North Park Ave',
    '789 South Ridge Rd',
]);

test('it calculates far distance tier for unrecognized addresses', function () {
    $result = resolve(DeliveryRouteService::class)->calculateDistanceTier('1234 Unknown Rd');

    expect($result)
        ->tier->toBe('Far')
        ->color->toBe('red')
        ->estimated_minutes->toBe(35);
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

test('it excludes orders with empty string delivery address', function () {
    $date = now()->format('Y-m-d');

    Order::factory()->create([
        'delivery_date' => $date,
        'delivery_address' => '',
    ]);

    $result = resolve(DeliveryRouteService::class)->loadOrders($date);

    expect($result)->toBeEmpty();
});

test('it returns route stats from delivery orders', function () {
    $orders = collect([
        [
            'id' => 1,
            'total' => 50.00,
            'distance_tier' => ['tier' => 'Close', 'color' => 'green', 'estimated_minutes' => 10],
        ],
        [
            'id' => 2,
            'total' => 30.00,
            'distance_tier' => ['tier' => 'Far', 'color' => 'red', 'estimated_minutes' => 35],
        ],
    ]);

    $stats = resolve(DeliveryRouteService::class)->getRouteStats($orders);

    expect($stats)
        ->total_orders->toBe(2)
        ->total_revenue->toBe(80.0)
        ->estimated_total_time->toBe(45)
        ->average_distance_time->toBe(22.5);
});

test('it returns route stats for empty collection', function () {
    $stats = resolve(DeliveryRouteService::class)->getRouteStats(collect());

    expect($stats)
        ->total_orders->toBe(0)
        ->total_revenue->toBe(0)
        ->estimated_total_time->toBe(0)
        ->average_distance_time->toBe(0.0);
});
