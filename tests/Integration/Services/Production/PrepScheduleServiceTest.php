<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Production\PrepScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('loadWeeklyData returns 7 weekDays', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');

    $result = resolve(PrepScheduleService::class)->loadWeeklyData($monday);

    expect($result->weekDays)->toHaveCount(7);
});

test('loadWeeklyData groups orders by delivery date', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $tuesday = now()->startOfWeek()->addDay()->format('Y-m-d');

    Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
    ]);

    Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '14:00',
    ]);

    Order::factory()->create([
        'delivery_date' => $tuesday,
        'delivery_time' => '09:00',
    ]);

    $result = resolve(PrepScheduleService::class)->loadWeeklyData($monday);

    expect($result->weeklyOrders)
        ->toHaveCount(2)
        ->and($result->weeklyOrders[$monday])->toHaveCount(2)
        ->and($result->weeklyOrders[$tuesday])->toHaveCount(1);
});

test('generatePrepSchedule creates tasks for items with recipes', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $product = Product::factory()->create();
    Recipe::factory()->create([
        'product_id' => $product->id,
        'prep_time_minutes' => 45,
    ]);

    $order = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $service = resolve(PrepScheduleService::class);
    $data = $service->loadWeeklyData($monday);
    $prepSchedule = $data->prepSchedule;

    expect($prepSchedule)->toHaveCount(1)
        ->and($prepSchedule[$monday])->toHaveCount(1)
        ->and($prepSchedule[$monday][0])
        ->product_name->toBe($product->name)
        ->quantity->toBe(2)
        ->prep_time_minutes->toBe(45);
});

test('generatePrepSchedule skips items without recipes', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $product = Product::factory()->create();

    $order = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $service = resolve(PrepScheduleService::class);
    $data = $service->loadWeeklyData($monday);

    expect($data->prepSchedule)->toBeEmpty();
});

test('getProductSummary aggregates quantities across orders', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $product = Product::factory()->create();

    $orderA = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
    ]);
    OrderItem::factory()->create([
        'order_id' => $orderA->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $orderB = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '14:00',
    ]);
    OrderItem::factory()->create([
        'order_id' => $orderB->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $service = resolve(PrepScheduleService::class);
    $data = $service->loadWeeklyData($monday);
    $summary = $service->getProductSummary($data->weeklyOrders);

    expect($summary[$product->name])
        ->total_quantity->toBe(8)
        ->orders_count->toBe(2);
});

test('getTotalPrepHours calculates total from prep schedule', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $productA = Product::factory()->create();
    Recipe::factory()->create([
        'product_id' => $productA->id,
        'prep_time_minutes' => 60,
    ]);

    $productB = Product::factory()->create();
    Recipe::factory()->create([
        'product_id' => $productB->id,
        'prep_time_minutes' => 90,
    ]);

    $order = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '12:00',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productA->id,
        'quantity' => 1,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productB->id,
        'quantity' => 1,
    ]);

    $service = resolve(PrepScheduleService::class);
    $data = $service->loadWeeklyData($monday);
    $totalHours = $service->getTotalPrepHours($data->prepSchedule);

    expect($totalHours)->toBe(2.5);
});

test('getTimelineView formats prep tasks for display', function () {
    $monday = now()->startOfWeek()->format('Y-m-d');
    $product = Product::factory()->create();
    Recipe::factory()->create([
        'product_id' => $product->id,
        'prep_time_minutes' => 30,
    ]);

    $order = Order::factory()->create([
        'delivery_date' => $monday,
        'delivery_time' => '10:00',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $service = resolve(PrepScheduleService::class);
    $data = $service->loadWeeklyData($monday);
    $timeline = $service->getTimelineView($data->prepSchedule);

    expect($timeline)->toHaveCount(1)
        ->and($timeline[$monday]->firstOrFail())
        ->toHaveKeys(['time', 'task', 'duration', 'order', 'delivery_time'])
        ->duration->toBe(30)
        ->delivery_time->toBe('10:00');
});

test('getWeekSummary calculates totals from orders', function () {
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
    $summary = $service->getWeekSummary($data->weeklyOrders, $data->prepSchedule);

    expect($summary)
        ->total_orders->toBe(1)
        ->total_items->toBe(3);
});
