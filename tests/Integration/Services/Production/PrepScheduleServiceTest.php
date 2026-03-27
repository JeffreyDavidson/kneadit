<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Production\PrepScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it loads weekly data with orders grouped by date', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');

    Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
    ]);

    $result = resolve(PrepScheduleService::class)->loadWeeklyData($monday);

    expect($result)
        ->toHaveKeys(['weeklyOrders', 'weekDays', 'prepSchedule'])
        ->weekDays->toHaveCount(7)
        ->weeklyOrders->toHaveCount(1);
});

test('it calculates week summary from orders', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $product = Product::factory()->create();

    $order = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
        'total' => 50.00,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $service = resolve(PrepScheduleService::class);
    $data = $service->loadWeeklyData($monday);
    $summary = $service->getWeekSummary($data['weeklyOrders'], $data['prepSchedule']);

    expect($summary)
        ->total_orders->toBe(1)
        ->total_items->toBe(3);
});
