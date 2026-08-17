<?php

use App\Actions\Orders\RestockIngredientsForOrder;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('restocks ingredient stock based on recipe quantities and order item quantities', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create(['name' => 'Sourdough Recipe']);
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 50.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.5, 'unit' => 'kg']);

    $order = Order::factory()->cancelled()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 3, 'unit_price' => 10.00]);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($flour->refresh()->current_stock)->toBe('51.50');
});

test('writes a Restock stock-adjustment row tagged with the order number', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $sugar = Ingredient::factory()->create(['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($sugar->id, ['quantity' => 0.25, 'unit' => 'kg']);

    $order = Order::factory()->cancelled()->create(['order_number' => 'TEST-RESTOCK-001']);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 4, 'unit_price' => 5.00]);

    resolve(RestockIngredientsForOrder::class)($order);

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => $sugar->id,
        'quantity' => 1.00,
        'type' => 'restock',
        'notes' => 'Order #TEST-RESTOCK-001 cancelled',
    ]);
});

test('skips order items whose product has been deleted', function () {
    $order = Order::factory()->cancelled()->create();
    $orphanItem = OrderItem::factory()->recycle($order)->create(['product_id' => null, 'quantity' => 5]);
    $unrelatedIngredient = Ingredient::factory()->create(['current_stock' => 10.00]);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($unrelatedIngredient->refresh()->current_stock)->toBe('10.00');
});

test('handles multiple ingredients per recipe and multiple recipes per product', function () {
    $product = Product::factory()->create();
    $recipeA = Recipe::factory()->for($product)->create();
    $recipeB = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 20.00]);
    $butter = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 5.00]);
    $recipeA->inventoryIngredients()->attach($flour->id, ['quantity' => 1.0, 'unit' => 'kg']);
    $recipeB->inventoryIngredients()->attach($butter->id, ['quantity' => 0.2, 'unit' => 'kg']);

    $order = Order::factory()->cancelled()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 8.00]);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($flour->refresh()->current_stock)->toBe('22.00')
        ->and($butter->refresh()->current_stock)->toBe('5.40');
});
