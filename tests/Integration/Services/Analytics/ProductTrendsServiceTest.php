<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Analytics\ProductTrendsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

/**
 * @param array<int, array<string, mixed>> $result
 * @return array{name: mixed, current: mixed, previous: mixed, change: mixed, trend: mixed}
 */
function trendProduct(array $result, int $categoryIndex = 0): array
{
    $product = data_get($result, "{$categoryIndex}.products.0");
    throw_unless(is_array($product), RuntimeException::class, 'Expected product trend data.');

    return [
        'name' => data_get($product, 'name'),
        'current' => data_get($product, 'current'),
        'previous' => data_get($product, 'previous'),
        'change' => data_get($product, 'change'),
        'trend' => data_get($product, 'trend'),
    ];
}

/**
 * @param array<int, array<string, mixed>> $result
 * @return array<string, mixed>
 */
function trendCategory(array $result, int $index): array
{
    $category = $result[$index] ?? null;
    throw_unless(is_array($category), RuntimeException::class, 'Expected product trend category data.');

    return $category;
}

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

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result)
        ->toBeArray()
        ->toHaveCount(1)
        ->and(trendProduct($result))
        ->name->toBe($product->name)
        ->current->toBe(5);
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
    $productData = trendProduct($result);

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

    expect(trendProduct($result)['trend'])->toBe('up');
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

    expect(trendProduct($result)['trend'])->toBe('down');
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

    expect(trendProduct($result)['trend'])->toBe('flat');
});

test('excludes products with zero orders in both months', function () {
    $category = Category::factory()->create();
    Product::factory()->inCategory($category)->create();

    $result = (new ProductTrendsService)->calculate(2026, 3);

    expect($result)->toBeEmpty();
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
        ->and(trendCategory($result, 0)['category'])->toBe('Breads')
        ->and(trendCategory($result, 1)['category'])->toBe('Pastries');
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
