<?php

use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\CapacityExceededException;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Services\Inventory\InventoryManager;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    Mail::fake();
    test()->user = User::factory()->owner()->create();
    settings(['default_daily_capacity' => '10']);
});

test('guardCapacity throws when date is at capacity', function () {
    $date = Date::tomorrow();
    settings(['default_daily_capacity' => '1']);

    Order::factory()
        ->recycle(test()->user)
        ->create(['delivery_date' => $date, 'status' => OrderStatus::Confirmed]);

    resolve(InventoryManager::class)->guardCapacity($date);
})->throws(CapacityExceededException::class);

test('guardCapacity does not throw when capacity available', function () {
    $date = Date::tomorrow();

    resolve(InventoryManager::class)->guardCapacity($date);

    expect(true)->toBeTrue();
});

test('deductForOrder deducts ingredient stock', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100.00]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.5, 'unit' => 'kg']);

    $order = Order::factory()
        ->recycle(test()->user)
        ->create(['status' => OrderStatus::Baking]);
    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 10.00,
    ]);

    resolve(InventoryManager::class)->deductForOrder($order);

    expect($flour->refresh()->current_stock)->toBe('98.50');
});
