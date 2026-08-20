<?php

use App\Actions\Orders\DeductIngredientsForOrder;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

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

    $order = Order::factory()->baking()->create();

    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 10.00,
    ]);

    resolve(DeductIngredientsForOrder::class)($order);

    expect($flour->refresh()->current_stock)->toBe('98.50');
});
