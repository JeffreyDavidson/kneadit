<?php

use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Queries\Financial\ProductSalesQuery;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('topByRevenue returns products sorted by revenue', function () {
    $expensive = Product::factory()->create();
    $cheap = Product::factory()->create();
    $order = Order::factory()->create(['delivery_date' => now()]);

    OrderItem::factory()->recycle($order, $expensive)->create(['quantity' => 1, 'unit_price' => 50.00]);
    OrderItem::factory()->recycle($order, $cheap)->create(['quantity' => 5, 'unit_price' => 3.00]);

    $range = new DateRange(now()->subDay(), now()->addDay());
    $result = ProductSalesQuery::topByRevenue($range);

    expect($result)->toHaveCount(2)
        ->and($result->first()['name'])->toBe($expensive->name);
});

test('topByQuantity returns products sorted by quantity sold', function () {
    $highQuantity = Product::factory()->create();
    $lowQuantity = Product::factory()->create();
    $order = Order::factory()->create(['delivery_date' => now()]);

    OrderItem::factory()->recycle($order, $highQuantity)->create(['quantity' => 20, 'unit_price' => 2.00]);
    OrderItem::factory()->recycle($order, $lowQuantity)->create(['quantity' => 1, 'unit_price' => 100.00]);

    $range = new DateRange(now()->subDay(), now()->addDay());
    $result = ProductSalesQuery::topByQuantity($range);

    expect($result)->toHaveCount(2)
        ->and($result->first()['name'])->toBe($highQuantity->name)
        ->and($result->first()['units_sold'])->toBe(20)
        ->and($result->last()['name'])->toBe($lowQuantity->name)
        ->and($result->last()['units_sold'])->toBe(1);
});

test('topByQuantity excludes cancelled orders', function () {
    $product = Product::factory()->create();
    $activeOrder = Order::factory()->create(['delivery_date' => now()]);
    $cancelledOrder = Order::factory()->cancelled()->create(['delivery_date' => now()]);

    OrderItem::factory()->recycle($activeOrder, $product)->create(['quantity' => 3, 'unit_price' => 10.00]);
    OrderItem::factory()->recycle($cancelledOrder, $product)->create(['quantity' => 50, 'unit_price' => 10.00]);

    $range = new DateRange(now()->subDay(), now()->addDay());
    $result = ProductSalesQuery::topByQuantity($range);

    expect($result)->toHaveCount(1)
        ->and($result->first()['units_sold'])->toBe(3);
});

test('topByQuantity respects the limit parameter', function () {
    $order = Order::factory()->create(['delivery_date' => now()]);

    $products = Product::factory()->count(5)->create();
    $products->each(function (Product $product, int $index) use ($order) {
        OrderItem::factory()->recycle($order, $product)->create([
            'quantity' => ($index + 1) * 2,
            'unit_price' => 5.00,
        ]);
    });

    $range = new DateRange(now()->subDay(), now()->addDay());
    $result = ProductSalesQuery::topByQuantity($range, 3);

    expect($result)->toHaveCount(3);
});

test('topByQuantity excludes orders outside date range', function () {
    $inRange = Product::factory()->create();
    $outOfRange = Product::factory()->create();

    $orderInRange = Order::factory()->create(['delivery_date' => now()]);
    $orderOutOfRange = Order::factory()->create(['delivery_date' => now()->subMonth()]);

    OrderItem::factory()->recycle($orderInRange, $inRange)->create(['quantity' => 5, 'unit_price' => 10.00]);
    OrderItem::factory()->recycle($orderOutOfRange, $outOfRange)->create(['quantity' => 100, 'unit_price' => 10.00]);

    $range = new DateRange(now()->subDay(), now()->addDay());
    $result = ProductSalesQuery::topByQuantity($range);

    expect($result)->toHaveCount(1)
        ->and($result->first()['name'])->toBe($inRange->name);
});
