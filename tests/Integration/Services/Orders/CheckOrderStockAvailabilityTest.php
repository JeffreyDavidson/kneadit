<?php

use App\Exceptions\Orders\InsufficientStockException;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Orders\CheckOrderStockAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('passes silently when projected demand fits inside stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['current_stock' => 100.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 10]);

    resolve(CheckOrderStockAvailability::class)($order);
})->throwsNoExceptions();

test('throws when single ingredient is short', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 3]);

    expect(fn () => resolve(CheckOrderStockAvailability::class)($order))
        ->toThrow(InsufficientStockException::class, 'Flour');
});

test('aggregates demand across multiple items sharing an ingredient', function () {
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();
    $recipeA = Recipe::factory()->for($productA)->create();
    $recipeB = Recipe::factory()->for($productB)->create();
    $shared = Ingredient::factory()->create(['name' => 'Butter', 'current_stock' => 5.00]);
    $recipeA->inventoryIngredients()->attach($shared->id, ['quantity' => 2.0, 'unit' => 'lb']);
    $recipeB->inventoryIngredients()->attach($shared->id, ['quantity' => 2.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $productA->id, 'quantity' => 2]);
    OrderItem::factory()->for($order)->create(['product_id' => $productB->id, 'quantity' => 1]);

    // Combined demand 6 lb > 5 lb on hand
    expect(fn () => resolve(CheckOrderStockAvailability::class)($order))
        ->toThrow(InsufficientStockException::class, 'Butter');
});

test('lists every shortage in the exception message', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $a = Ingredient::factory()->create(['name' => 'Vanilla', 'current_stock' => 0.50]);
    $b = Ingredient::factory()->create(['name' => 'Cocoa', 'current_stock' => 0.50]);
    $recipe->inventoryIngredients()->attach($a->id, ['quantity' => 1.0, 'unit' => 'oz']);
    $recipe->inventoryIngredients()->attach($b->id, ['quantity' => 1.0, 'unit' => 'oz']);

    $order = Order::factory()->pending()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2]);

    expect(fn () => resolve(CheckOrderStockAvailability::class)($order))
        ->toThrow(InsufficientStockException::class, 'Vanilla, Cocoa');
});

test('skips items whose product was deleted', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $sugar = Ingredient::factory()->create(['name' => 'Sugar', 'current_stock' => 5.00]);
    $recipe->inventoryIngredients()->attach($sugar->id, ['quantity' => 1.0, 'unit' => 'lb']);

    $order = Order::factory()->pending()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2]);

    // Item with a soft-deleted product should not contribute to ingredient draw.
    $deletedProduct = Product::factory()->create();
    OrderItem::factory()->for($order)->create(['product_id' => $deletedProduct->id, 'quantity' => 100]);
    $deletedProduct->delete();

    resolve(CheckOrderStockAvailability::class)($order);
})->throwsNoExceptions();
