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
    $recipe = Recipe::factory()->for($product)->create([
        'name' => 'Sourdough Recipe',
    ]);
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100.00]);

    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.5, 'unit' => 'kg']);

    $order = Order::factory()->create(['status' => OrderStatus::Baking]);

    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 10.00,
    ]);

    resolve(DeductIngredients::class)($order);

    expect($flour->fresh()->current_stock)->toBe('98.50');
});
