<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Analytics\ProductTrendsService;
use App\Services\Settings\TenantSettings;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns products with order counts for the given month', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    $order = Order::factory()->create([
        'created_at' => '2026-03-15 10:00:00',
        'status' => OrderStatus::Pending,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);
    Product::factory()->inCategory($category)->create();

    $productQuerySql = '';
    $productQueryBindings = [];
    DB::listen(function (QueryExecuted $query) use (&$productQuerySql, &$productQueryBindings): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_contains($sql, 'from products')) {
            $productQuerySql = $sql;
            $productQueryBindings = $query->bindings;
        }
    });

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]['products'])->toHaveCount(1)
        ->and($result[0]['products'][0])
        ->name->toBe($product->name)
        ->current->toBe(5)
        ->and($productQuerySql)->toContain('products.id in (?)')
        ->and(array_slice($productQueryBindings, -1))->toBe([$product->id]);
});

test('includes change percentage comparing to previous month', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    $prevOrder = Order::factory()->create([
        'created_at' => '2026-02-10 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $prevOrder->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $currentOrder = Order::factory()->create([
        'created_at' => '2026-03-15 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $currentOrder->id,
        'product_id' => $product->id,
        'quantity' => 15,
    ]);

    $result = (new ProductTrendsService)->calculate(2026, 3);
    $productData = $result[0]['products'][0];

    expect($productData)
        ->current->toBe(15)
        ->previous->toBe(10)
        ->change->toBe(50.0);
});

test('sets trend to up when current exceeds previous', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    $prevOrder = Order::factory()->create([
        'created_at' => '2026-02-10 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $prevOrder->id,
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $currentOrder = Order::factory()->create([
        'created_at' => '2026-03-15 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $currentOrder->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result[0]['products'][0]['trend'])->toBe('up');
});

test('sets trend to down when current is less than previous', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    $prevOrder = Order::factory()->create([
        'created_at' => '2026-02-10 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $prevOrder->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $currentOrder = Order::factory()->create([
        'created_at' => '2026-03-15 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $currentOrder->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result[0]['products'][0]['trend'])->toBe('down');
});

test('sets trend to flat when current equals previous', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    $prevOrder = Order::factory()->create([
        'created_at' => '2026-02-10 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $prevOrder->id,
        'product_id' => $product->id,
        'quantity' => 7,
    ]);

    $currentOrder = Order::factory()->create([
        'created_at' => '2026-03-15 10:00:00',
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $currentOrder->id,
        'product_id' => $product->id,
        'quantity' => 7,
    ]);

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result[0]['products'][0]['trend'])->toBe('flat');
});

test('excludes products with zero orders in both months', function () {
    $category = Category::factory()->create();
    Product::factory()->inCategory($category)->create();

    $catalogQueries = 0;
    DB::listen(function (QueryExecuted $query) use (&$catalogQueries): void {
        $sql = strtolower(str_replace(['"', '`', '[', ']'], '', $query->sql));

        if (str_contains($sql, 'from categories') || str_contains($sql, 'from products')) {
            $catalogQueries++;
        }
    });

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result)->toBeEmpty()
        ->and($catalogQueries)->toBe(0);
});

test('groups products by category', function () {
    $categoryA = Category::factory()->create(['name' => 'Breads', 'sort_order' => 1]);
    $categoryB = Category::factory()->create(['name' => 'Pastries', 'sort_order' => 2]);

    $productA = Product::factory()->inCategory($categoryA)->create();
    $productB = Product::factory()->inCategory($categoryB)->create();

    $order = Order::factory()->create([
        'created_at' => '2026-03-15 10:00:00',
        'status' => OrderStatus::Pending,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productA->id,
        'quantity' => 3,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productB->id,
        'quantity' => 2,
    ]);

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result)
        ->toHaveCount(2)
        ->and($result[0]['category'])->toBe('Breads')
        ->and($result[1]['category'])->toBe('Pastries');
});

test('excludes cancelled orders from counts', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    $cancelledOrder = Order::factory()->cancelled()->create([
        'created_at' => '2026-03-15 10:00:00',
    ]);
    OrderItem::factory()->create([
        'order_id' => $cancelledOrder->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result)->toBeEmpty();
});

test('counts orders by the bakery-local month they were placed in', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    $category = Category::factory()->create();
    $product = Product::factory()->inCategory($category)->create();

    // 8:30 pm on Oct 31 in New York, which is already November in UTC.
    $lastNight = Order::factory()->create(['created_at' => '2026-11-01 00:30:00', 'status' => OrderStatus::Pending]);
    OrderItem::factory()->recycle($lastNight, $product)->create(['quantity' => 4]);
    // 11:30 pm on Sep 30 in New York, which is already October in UTC.
    $septemberEvening = Order::factory()->create(['created_at' => '2026-10-01 03:30:00', 'status' => OrderStatus::Pending]);
    OrderItem::factory()->recycle($septemberEvening, $product)->create(['quantity' => 3]);

    $result = (new ProductTrendsService)->calculate(2026, 10);

    expect($result[0]['products'][0])
        ->current->toBe(4)
        ->previous->toBe(3);
});
