<?php

use App\Actions\Orders\ModifyOrder;
use App\Events\Orders\OrderModified;
use App\Exceptions\Orders\InsufficientStockException;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['order_modification_window_minutes' => 30]);
});

test('updates quantities and recalculates totals', function () {
    Event::fake();

    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create([
        'subtotal' => 20.00,
        'total' => 20.00,
    ]);
    $item = OrderItem::factory()->for($order)->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 10.00,
    ]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 5],
    ]);

    $order->refresh();
    expect($order->orderItems()->first()->quantity)->toBe(5)
        ->and($order->subtotal->dollars())->toBe(50.00)
        ->and($order->total->dollars())->toBe(50.00);
    Event::assertDispatched(OrderModified::class);
});

test('removes item when quantity is set to zero', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create();
    $keep = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);
    $remove = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $keep->id, 'quantity' => 1],
        ['order_item_id' => $remove->id, 'quantity' => 0],
    ]);

    $order->refresh();
    expect($order->orderItems)->toHaveCount(1)
        ->and($order->subtotal->dollars())->toBe(10.00);
});

test('updates tip when provided', function () {
    $product = Product::factory()->create(['price' => 10.00]);
    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ], tipAmount: 4.50);

    $order->refresh();
    expect($order->tip_amount->dollars())->toBe(4.50)
        ->and($order->total->dollars())->toBe(24.50);
});

test('throws when order is not in pending status', function () {
    $order = Order::factory()->confirmed()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when order is already paid', function () {
    $order = Order::factory()->pending()->paid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when modification window has expired', function () {
    settings(['order_modification_window_minutes' => 5]);

    $order = Order::factory()->pending()->unpaid()->create([
        'created_at' => now()->subMinutes(10),
    ]);
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('throws when feature is disabled (window = 0)', function () {
    settings(['order_modification_window_minutes' => 0]);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]))->toThrow(OrderNotModifiableException::class);
});

test('rolls back when modification would leave order with no items', function () {
    $order = Order::factory()->pending()->unpaid()->create();
    $only = OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $only->id, 'quantity' => 0],
    ]))->toThrow(OrderNotModifiableException::class)
        ->and($order->fresh()->orderItems()->count())->toBe(1);
});

test('throws InsufficientStockException when modification exceeds ingredient stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 6],
    ]))->toThrow(
        InsufficientStockException::class,
        'insufficient stock for Flour',
    )
        ->and($order->fresh()->orderItems()->first()->quantity)->toBe(2);
});

test('rolls back item-quantity changes when stock check fails', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $sugar = Ingredient::factory()->create(['name' => 'Sugar', 'current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($sugar->id, ['quantity' => 1.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 10.00]);

    try {
        resolve(ModifyOrder::class)($order, [
            ['order_item_id' => $item->id, 'quantity' => 10],
        ]);
    } catch (InsufficientStockException) {
        // expected
    }

    expect($order->fresh()->orderItems()->first()->quantity)->toBe(3);
});

test('reports every shortage when several ingredients fall short', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $butter = Ingredient::factory()->create(['name' => 'Butter', 'current_stock' => 2.00]);
    $eggs = Ingredient::factory()->create(['name' => 'Eggs', 'current_stock' => 4.00]);
    $recipe->inventoryIngredients()->attach($butter->id, ['quantity' => 1.0, 'unit' => 'lb']);
    $recipe->inventoryIngredients()->attach($eggs->id, ['quantity' => 2.0, 'unit' => 'each']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10.00]);

    expect(fn () => resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 5],
    ]))->toThrow(InsufficientStockException::class, 'Butter, Eggs');
});

test('allows modification when stock is sufficient', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 100.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 10.00]);

    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 10],
    ]);

    expect($order->fresh()->orderItems()->first()->quantity)->toBe(10);
});

test('allows decreasing quantity even when current order draw exceeds stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 4.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->unpaid()->create();
    $item = OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 10.00]);

    // Current order would need 10 lb; only 4 lb on hand. Decreasing to 2 needs
    // 4 lb — fits exactly, so the modification must be allowed.
    resolve(ModifyOrder::class)($order, [
        ['order_item_id' => $item->id, 'quantity' => 2],
    ]);

    expect($order->fresh()->orderItems()->first()->quantity)->toBe(2);
});
