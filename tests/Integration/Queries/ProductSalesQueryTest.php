<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Queries\ProductSalesQuery;
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
