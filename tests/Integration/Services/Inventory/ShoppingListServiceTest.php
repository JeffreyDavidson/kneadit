<?php

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Supplier;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Inventory\ShoppingListService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates shopping list with low stock ingredients', function () {
    Ingredient::factory()->lowStock()->create(['name' => 'Flour']);
    Ingredient::factory()->create(['current_stock' => 100, 'low_stock_threshold' => 5]);

    $service = resolve(ShoppingListService::class);
    $result = $service->generate();

    expect($result)->toBeArray()->not->toBeEmpty();
});

test('returns empty list when all stock is sufficient', function () {
    Ingredient::factory()->create(['current_stock' => 100, 'low_stock_threshold' => 5]);

    $service = resolve(ShoppingListService::class);
    $result = $service->generate();

    expect($result)->toBeArray()->toBeEmpty();
});

test('includes upcoming order needs when enabled with date range', function () {
    $ingredient = Ingredient::factory()->lowStock()->create(['name' => 'Flour']);

    $product = Product::factory()->create();

    // Create recipe and attach ingredient
    $recipe = $product->recipe()->create([
        'name' => 'Sourdough Recipe',
        'ingredients' => json_encode([['name' => 'Flour', 'quantity' => 2]]),
        'instructions' => 'Mix and bake.',
    ]);
    $recipe->inventoryIngredients()->attach($ingredient->id, ['quantity' => 2.0, 'unit' => 'kg']);

    $order = Order::factory()->confirmed()->create([
        'delivery_date' => now()->addDays(2)->format('Y-m-d'),
    ]);
    OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 10.00,
    ]);

    $service = resolve(ShoppingListService::class);
    $result = $service->generate(
        includeUpcoming: true,
        startDate: now()->format('Y-m-d'),
        endDate: now()->addWeek()->format('Y-m-d'),
    );

    expect($result)->toBeArray()->not->toBeEmpty();
});

test('upcoming order ingredient needs increase the suggested quantity', function () {
    // Regression: ShoppingListService.calculateUpcomingNeeds previously queried
    // a non-existent `pickup_date` column on orders (the column is `delivery_date`).
    // The original "includes upcoming order needs" test asserted only that the
    // result wasn't empty — the low-stock ingredient alone made that true even
    // when the upcoming-orders branch silently failed. This test asserts that
    // the upcoming-needs aggregation actually contributes to the suggested
    // quantity, which would fail if the date column ever drifts again.
    $ingredient = Ingredient::factory()->lowStock()->create([
        'name' => 'Flour',
        'current_stock' => 0,
        'low_stock_threshold' => 5,
    ]);
    $product = Product::factory()->create();
    $product->recipe()->create([
        'name' => 'Loaf',
        'ingredients' => json_encode([['name' => 'Flour', 'quantity' => 4]]),
        'instructions' => 'Mix.',
    ])->inventoryIngredients()->attach($ingredient->id, ['quantity' => 4.0, 'unit' => 'kg']);

    $order = Order::factory()->confirmed()->create([
        'delivery_date' => now()->addDays(3)->format('Y-m-d'),
    ]);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 5, 'unit_price' => 10.00]);

    // Without upcoming: just the low-stock baseline (threshold * 2 - current).
    $without = resolve(ShoppingListService::class)->generate(includeUpcoming: false);
    // With upcoming for the order's date range: should add 5 * 4 = 20kg.
    $with = resolve(ShoppingListService::class)->generate(
        includeUpcoming: true,
        startDate: now()->format('Y-m-d'),
        endDate: now()->addWeek()->format('Y-m-d'),
    );

    $needWithout = collect($without)->flatMap(fn (array $g) => $g['items'])->firstWhere('ingredient_id', $ingredient->id)['needed'] ?? 0;
    $needWith = collect($with)->flatMap(fn (array $g) => $g['items'])->firstWhere('ingredient_id', $ingredient->id)['needed'] ?? 0;

    expect($needWith)->toBe($needWithout + 20.0);
});

test('skips upcoming needs when not enabled', function () {
    Ingredient::factory()->lowStock()->create(['name' => 'Flour']);

    $service = resolve(ShoppingListService::class);
    $resultWithout = $service->generate(includeUpcoming: false);
    $resultWith = $service->generate(includeUpcoming: true, startDate: null, endDate: null);

    // Both should return similar results since upcoming is disabled or missing dates
    expect($resultWithout)->toBeArray();
    expect($resultWith)->toBeArray();
});

test('groups ingredients by supplier with best price', function () {
    $supplier = Supplier::factory()->create(['name' => 'Best Flour Co']);
    $ingredient = Ingredient::factory()->lowStock()->create([
        'name' => 'Flour',
        'cost_per_unit' => 2.00,
    ]);

    $ingredient->suppliers()->attach($supplier->id, [
        'unit_price' => 1.50,
        'minimum_order' => 10,
        'lead_time_days' => 3,
        'sku' => 'FL-001',
    ]);

    $service = resolve(ShoppingListService::class);
    $result = $service->generate();

    expect($result)->toHaveKey($supplier->id)
        ->and($result[$supplier->id]['supplier']['name'])->toBe('Best Flour Co')
        ->and($result[$supplier->id]['items'])->not->toBeEmpty();
});

test('reads supplier prices written in dollars through the pivot as dollars', function () {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->lowStock()->create(['cost_per_unit' => 9.00]);
    $ingredient->suppliers()->attach($supplier->id, ['unit_price' => 1.50, 'minimum_order' => 25.00]);

    $item = resolve(ShoppingListService::class)->generate()[$supplier->id]['items'][0];

    expect($item['unit_price'])->toBe(1.5)
        ->and($item['minimum_order'])->toBe(25.0)
        ->and($item['subtotal'])->toBe(round($item['needed'] * 1.5, 2));
});

test('picks the supplier with the lowest pivot price', function () {
    $ingredient = Ingredient::factory()->lowStock()->create();
    $dear = Supplier::factory()->create();
    $cheap = Supplier::factory()->create();
    $ingredient->suppliers()->attach($dear->id, ['unit_price' => 12.00]);
    $ingredient->suppliers()->attach($cheap->id, ['unit_price' => 0.95]);

    $result = resolve(ShoppingListService::class)->generate();

    expect($result)->toHaveKey($cheap->id)->not->toHaveKey($dear->id)
        ->and($result[$cheap->id]['items'][0]['unit_price'])->toBe(0.95);
});

test('accumulates supplier totals from rounded item subtotals', function () {
    $supplier = Supplier::factory()->create();

    foreach ([0.10, 0.20, 0.33] as $unitPrice) {
        $ingredient = Ingredient::factory()->lowStock()->create();
        $ingredient->suppliers()->attach($supplier->id, [
            'unit_price' => $unitPrice,
            'minimum_order' => null,
            'lead_time_days' => 3,
            'sku' => 'ING-'.$ingredient->id,
        ]);
    }

    $result = resolve(ShoppingListService::class)->generate();

    expect($result[$supplier->id]['items'])->toHaveCount(3)
        ->and(array_column($result[$supplier->id]['items'], 'subtotal'))->toBe([0.7, 1.4, 2.31])
        ->and($result[$supplier->id]['total'])->toBe(4.41);
});

test('ingredients without suppliers go into no supplier group', function () {
    Ingredient::factory()->lowStock()->create(['name' => 'Flour']);

    $service = resolve(ShoppingListService::class);
    $result = $service->generate();

    expect($result)->toHaveKey('none')
        ->and($result['none']['supplier']['name'])->toBe('No Supplier Assigned');
});

test('skips ingredient when needed quantity is zero', function () {
    // current_stock equals low_stock_threshold * 2, so needed = 0
    Ingredient::factory()->create([
        'current_stock' => 0,
        'low_stock_threshold' => 0,
    ]);

    $service = resolve(ShoppingListService::class);
    $result = $service->generate();

    expect($result)->toBeEmpty();
});

test('upcoming order needs are expressed in the ingredient stock unit', function () {
    $ingredient = Ingredient::factory()->lowStock()->create([
        'name' => 'Flour',
        'unit' => 'kg',
        'current_stock' => 0,
        'low_stock_threshold' => 5,
    ]);
    $product = Product::factory()->create();
    $product->recipe()->create([
        'name' => 'Loaf',
        'ingredients' => json_encode([['name' => 'Flour', 'quantity' => 500]]),
        'instructions' => 'Mix.',
    ])->inventoryIngredients()->attach($ingredient->id, ['quantity' => 500, 'unit' => 'g']);

    $order = Order::factory()->confirmed()->create([
        'delivery_date' => now()->addDays(3)->format('Y-m-d'),
    ]);
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 4, 'unit_price' => 10.00]);

    $without = resolve(ShoppingListService::class)->generate(includeUpcoming: false);
    $with = resolve(ShoppingListService::class)->generate(
        includeUpcoming: true,
        startDate: now()->format('Y-m-d'),
        endDate: now()->addWeek()->format('Y-m-d'),
    );

    $needWithout = collect($without)->flatMap(fn (array $g) => $g['items'])->firstWhere('ingredient_id', $ingredient->id)['needed'];
    $needWith = collect($with)->flatMap(fn (array $g) => $g['items'])->firstWhere('ingredient_id', $ingredient->id)['needed'];

    expect($needWith)->toBe($needWithout + 2.0);
});
