<?php

use App\Actions\Orders\AdjustOrderIngredients;
use App\Enums\Inventory\StockAdjustmentType;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();

    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    test()->flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 10.00]);
    $recipe->inventoryIngredients()->attach(test()->flour->id, ['quantity' => 0.5, 'unit' => 'kg']);

    test()->order = Order::factory()->baking()->create(['order_number' => 'ADJ-001']);
    OrderItem::factory()->recycle(test()->order, $product)->create(['quantity' => 4]);
});

test('usage subtracts recipe quantities times order quantity and tags the adjustment with the order number', function () {
    resolve(AdjustOrderIngredients::class)(test()->order, StockAdjustmentType::Usage);

    expect(test()->flour->fresh()->current_stock)->toBe('8.00');

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => test()->flour->id,
        'quantity' => -2.00,
        'type' => 'usage',
        'notes' => 'Order #ADJ-001',
    ]);
});

test('restock adds the same quantity back and tags the adjustment as a cancellation', function () {
    resolve(AdjustOrderIngredients::class)(test()->order, StockAdjustmentType::Restock);

    expect(test()->flour->fresh()->current_stock)->toBe('12.00');

    assertDatabaseHas('stock_adjustments', [
        'ingredient_id' => test()->flour->id,
        'quantity' => 2.00,
        'type' => 'restock',
        'notes' => 'Order #ADJ-001 cancelled',
    ]);
});

test('usage followed by restock returns stock to its original level', function () {
    $adjust = resolve(AdjustOrderIngredients::class);

    $adjust(test()->order, StockAdjustmentType::Usage);
    $adjust(test()->order, StockAdjustmentType::Restock);

    expect(test()->flour->fresh()->current_stock)->toBe('10.00');
});

test('rejects adjustment types that are not usage or restock without touching stock', function (StockAdjustmentType $type) {
    $adjust = resolve(AdjustOrderIngredients::class);

    expect(fn () => $adjust(test()->order, $type))
        ->toThrow(InvalidArgumentException::class, 'Order ingredient adjustments must be usage or restock.')
        ->and(test()->flour->fresh()->current_stock)->toBe('10.00');
    assertDatabaseCount('stock_adjustments', 0);
})->with([
    'purchase' => StockAdjustmentType::Purchase,
    'adjustment' => StockAdjustmentType::Adjustment,
    'waste' => StockAdjustmentType::Waste,
]);
