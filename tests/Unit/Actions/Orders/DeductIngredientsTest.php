<?php

use App\Actions\Orders\DeductIngredients;
use App\Enums\OrderStatus;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('deducts ingredient stock based on recipe quantities and order item quantities', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::query()->create([
        'product_id' => $product->id,
        'name' => 'Sourdough Recipe',
        'ingredients' => '[]',
        'instructions' => 'Mix and bake',
    ]);
    $flour = Ingredient::query()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100.00]);

    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.5, 'unit' => 'kg']);

    $order = Order::factory()->create(['status' => OrderStatus::Baking]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 10.00,
    ]);

    resolve(DeductIngredients::class)($order);

    expect($flour->fresh()->current_stock)->toBe('98.50');
});
