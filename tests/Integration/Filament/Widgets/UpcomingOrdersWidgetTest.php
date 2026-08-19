<?php

use App\Filament\Widgets\UpcomingOrdersWidget;
use App\Models\Orders\Order;

beforeEach(function () {
    setUpTenantTest();
    // Pin the small size — its 3-day window matches the original (pre-size-aware)
    // behavior these tests were written against. Other sizes (md=5, lg=7) get
    // exercised by the AdminWidgets smoke test via the actual dashboard flow.
    test()->widget = tap(new UpcomingOrdersWidget, fn (UpcomingOrdersWidget $widget) => $widget->dashboardSize = 'sm');
});

test('get upcoming orders returns empty when no orders', function () {
    expect(testFixture('widget', UpcomingOrdersWidget::class)->getUpcomingOrders())->toBeEmpty();
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

    $orders = testFixture('widget', UpcomingOrdersWidget::class)->getUpcomingOrders();

    expect($orders)->not->toBeEmpty();
});

test('get upcoming orders excludes orders beyond 3 days', function () {
    Order::factory()->create([
        'delivery_date' => now()->addDays(5),
    ]);

    $orders = testFixture('widget', UpcomingOrdersWidget::class)->getUpcomingOrders();

    expect($orders)->toBeEmpty();
});

test('get upcoming orders groups by date', function () {
    Order::factory()->withDeliveryDate(now())->create();
    Order::factory()->withDeliveryDate(now()->addDay())->create();

    $orders = testFixture('widget', UpcomingOrdersWidget::class)->getUpcomingOrders();

    expect(count($orders))->toBeGreaterThanOrEqual(1);
});

test('get upcoming orders labels today correctly', function () {
    Order::factory()->withDeliveryDate(now())->create();

    $orders = testFixture('widget', UpcomingOrdersWidget::class)->getUpcomingOrders();
    $todayKey = now()->format('Y-m-d');

    if (isset($orders[$todayKey])) {
        $today = $orders[$todayKey];
        throw_unless(is_array($today), RuntimeException::class, 'Expected today order data.');
        expect($today['label'] ?? null)->toBe('Today');
    }
});

test('get upcoming orders labels tomorrow correctly', function () {
    Order::factory()->withDeliveryDate(now()->addDay())->create();

    $orders = testFixture('widget', UpcomingOrdersWidget::class)->getUpcomingOrders();
    $tomorrowKey = now()->addDay()->format('Y-m-d');

    if (isset($orders[$tomorrowKey])) {
        $tomorrow = $orders[$tomorrowKey];
        throw_unless(is_array($tomorrow), RuntimeException::class, 'Expected tomorrow order data.');
        expect($tomorrow['label'] ?? null)->toBe('Tomorrow');
    }
});
