<?php

use App\Actions\Orders\DeductIngredientsForOrder;
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

test('restocks the ingredient stock the order used', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create(['name' => 'Sourdough Recipe']);
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 50.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.5, 'unit' => 'kg']);
    $order = Order::factory()->baking()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 3, 'unit_price' => 10.00]);
    resolve(DeductIngredientsForOrder::class)($order);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($flour->fresh()->current_stock)->toBe('50.0000');
});

test('writes a Restock stock-adjustment row tagged with the order number', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $sugar = Ingredient::factory()->create(['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($sugar->id, ['quantity' => 0.25, 'unit' => 'kg']);
    $order = Order::factory()->baking()->create(['order_number' => 'TEST-RESTOCK-001']);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 4, 'unit_price' => 5.00]);
    resolve(DeductIngredientsForOrder::class)($order);

    resolve(RestockIngredientsForOrder::class)($order);

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => $sugar->id,
        'quantity' => 1.00,
        'type' => 'restock',
        'notes' => 'Order #TEST-RESTOCK-001 cancelled',
    ]);
});

test('restocks the amount deducted, not the amount the recipe now calls for', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 1.5, 'unit' => 'kg']);
    $order = Order::factory()->baking()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 1]);
    resolve(DeductIngredientsForOrder::class)($order);
    $recipe->inventoryIngredients()->updateExistingPivot($flour->id, ['quantity' => 0.3]);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($flour->fresh()->current_stock)->toBe('10.0000');
});

test('a second restock puts nothing back', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 1.5, 'unit' => 'kg']);
    $order = Order::factory()->baking()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 1]);
    resolve(DeductIngredientsForOrder::class)($order);
    $restock = resolve(RestockIngredientsForOrder::class);

    $restock($order);
    $restock($order);

    expect($flour->fresh()->current_stock)->toBe('10.0000');
});

test('does nothing for an order that never deducted stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 1.5, 'unit' => 'kg']);
    $order = Order::factory()->cancelled()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 1]);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($flour->fresh()->current_stock)->toBe('10.0000');
});

test('handles multiple ingredients per recipe and multiple recipes per product', function () {
    $product = Product::factory()->create();
    $recipeA = Recipe::factory()->for($product)->create();
    $recipeB = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 20.00]);
    $butter = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 5.00]);
    $recipeA->inventoryIngredients()->attach($flour->id, ['quantity' => 1.0, 'unit' => 'kg']);
    $recipeB->inventoryIngredients()->attach($butter->id, ['quantity' => 0.2, 'unit' => 'kg']);
    $order = Order::factory()->baking()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 8.00]);
    resolve(DeductIngredientsForOrder::class)($order);

    resolve(RestockIngredientsForOrder::class)($order);

    expect($flour->fresh()->current_stock)->toBe('20.0000')
        ->and($butter->fresh()->current_stock)->toBe('5.0000');
});
