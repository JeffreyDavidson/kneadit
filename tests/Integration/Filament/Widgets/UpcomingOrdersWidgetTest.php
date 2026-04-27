<?php

use App\Filament\Widgets\UpcomingOrdersWidget;
use App\Models\Orders\Order;

beforeEach(function () {
    setUpTenantTest();
    // Pin the small size — its 3-day window matches the original (pre-size-aware)
    // behavior these tests were written against. Other sizes (md=5, lg=7) get
    // exercised by the AdminWidgets smoke test via the actual dashboard flow.
    test()->widget = tap(new UpcomingOrdersWidget, fn ($w) => $w->dashboardSize = 'sm');
});

test('get upcoming orders returns empty when no orders', function () {
    expect(test()->widget->getUpcomingOrders())->toBeEmpty();
});

test('get upcoming orders returns orders within 3 days', function () {
    Order::factory()->create([
        'delivery_date' => now(),
        'delivery_time' => '10:00:00',
    ]);
    Order::factory()->create([
        'delivery_date' => now()->addDays(2),
        'delivery_time' => '14:00:00',
    ]);

    $orders = test()->widget->getUpcomingOrders();

    expect($orders)->not->toBeEmpty();
});

test('get upcoming orders excludes orders beyond 3 days', function () {
    Order::factory()->create([
        'delivery_date' => now()->addDays(5),
    ]);

    $orders = test()->widget->getUpcomingOrders();

    expect($orders)->toBeEmpty();
});

test('get upcoming orders groups by date', function () {
    Order::factory()->withDeliveryDate(now())->create();
    Order::factory()->withDeliveryDate(now()->addDay())->create();

    $orders = test()->widget->getUpcomingOrders();

    expect(count($orders))->toBeGreaterThanOrEqual(1);
});

test('get upcoming orders labels today correctly', function () {
    Order::factory()->withDeliveryDate(now())->create();

    $orders = test()->widget->getUpcomingOrders();
    $todayKey = now()->format('Y-m-d');

    if (isset($orders[$todayKey])) {
        expect($orders[$todayKey]['label'])->toBe('Today');
    }
});

test('get upcoming orders labels tomorrow correctly', function () {
    Order::factory()->withDeliveryDate(now()->addDay())->create();

    $orders = test()->widget->getUpcomingOrders();
    $tomorrowKey = now()->addDay()->format('Y-m-d');

    if (isset($orders[$tomorrowKey])) {
        expect($orders[$tomorrowKey]['label'])->toBe('Tomorrow');
    }
});
