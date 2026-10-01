<?php

use App\DataTransferObjects\Inventory\IngredientDemandItem;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Services\Inventory\IngredientDemandCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function demandProductNeeding(Ingredient $ingredient, float $quantityPerUnit, string $unit = 'kg'): Product
{
    $product = Product::factory()->create();
    Recipe::factory()->for($product)->create()
        ->inventoryIngredients()->attach($ingredient->id, ['quantity' => $quantityPerUnit, 'unit' => $unit]);

    return $product->refresh();
}

test('shortages is empty when there are no items', function () {
    $shortages = resolve(IngredientDemandCalculator::class)->shortages([]);

    expect($shortages)->toBeEmpty();
});

test('shortages is empty for a product without recipes', function () {
    $product = Product::factory()->create();

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 5)]);

    expect($shortages)->toBeEmpty();
});

test('shortages is empty when stock covers the demand', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 10]);
    $product = demandProductNeeding($flour, 2.0);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 3)]);

    expect($shortages)->toBeEmpty();
});

test('shortages is empty when demand exactly equals stock', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 6]);
    $product = demandProductNeeding($flour, 2.0);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 3)]);

    expect($shortages)->toBeEmpty();
});

test('shortages catches a demand a hundredth of the stock unit over the stock', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 0.002]);
    $product = demandProductNeeding($flour, 1.0, 'g');

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 3)]);

    expect($shortages)->toBe(['Flour']);
});

test('shortages names ingredients whose demand exceeds stock', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 5]);
    $product = demandProductNeeding($flour, 2.0);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 3)]);

    expect($shortages)->toBe(['Flour']);
});

test('shortages multiplies the recipe quantity by the ordered quantity', function (int $orderedQuantity, array $expected) {
    $butter = Ingredient::factory()->create(['name' => 'Butter', 'current_stock' => 1.0]);
    $product = demandProductNeeding($butter, 0.25);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, $orderedQuantity)]);

    expect($shortages)->toBe($expected);
})->with([
    'four units use exactly the stock' => [4, []],
    'five units exceed the stock' => [5, ['Butter']],
]);

test('shortages sums demand for an ingredient shared across items', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 5]);
    $sourdough = demandProductNeeding($flour, 1.0);
    $baguette = demandProductNeeding($flour, 1.0);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([
        new IngredientDemandItem($sourdough, 3),
        new IngredientDemandItem($baguette, 3),
    ]);

    expect($shortages)->toBe(['Flour']);
});

test('shortages only lists the ingredients that run short', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 100]);
    $yeast = Ingredient::factory()->create(['name' => 'Yeast', 'current_stock' => 1]);
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 1.0, 'unit' => 'kg']);
    $recipe->inventoryIngredients()->attach($yeast->id, ['quantity' => 0.5, 'unit' => 'kg']);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product->refresh(), 4)]);

    expect($shortages)->toBe(['Yeast']);
});

test('shortages draws on every recipe of a product', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 5]);
    $product = Product::factory()->create();
    Recipe::factory()->for($product)->create()
        ->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'kg']);
    Recipe::factory()->for($product)->create()
        ->inventoryIngredients()->attach($flour->id, ['quantity' => 2.0, 'unit' => 'kg']);

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product->refresh()->load('recipes.inventoryIngredients'), 2)]);

    expect($shortages)->toBe(['Flour']);
});

test('shortages accepts any iterable of items', function () {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'current_stock' => 1]);
    $product = demandProductNeeding($flour, 2.0);

    $items = (function () use ($product) {
        yield new IngredientDemandItem($product, 1);
    })();

    $shortages = resolve(IngredientDemandCalculator::class)->shortages($items);

    expect($shortages)->toBe(['Flour']);
});

test('shortages converts the recipe unit into the ingredient stock unit', function (float $stock, array $expected) {
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => $stock]);
    $product = demandProductNeeding($flour, 500, 'g');

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 2)]);

    expect($shortages)->toBe($expected);
})->with([
    'stock exactly covers 1 kg of demand' => [1.00, []],
    'stock just under 1 kg of demand' => [0.99, ['Flour']],
    'plenty of stock' => [10.00, []],
]);

test('shortages converts across count units', function (float $stock, array $expected) {
    $eggs = Ingredient::factory()->create(['name' => 'Eggs', 'unit' => 'dozen', 'current_stock' => $stock]);
    $product = demandProductNeeding($eggs, 6, 'each');

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 3)]);

    expect($shortages)->toBe($expected);
})->with([
    'a dozen and a half covers 18 eggs' => [1.5, []],
    'less than that falls short' => [1.4, ['Eggs']],
]);

test('shortages skips a line whose unit cannot be converted and logs a warning', function () {
    $logger = Log::spy();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'lbs', 'current_stock' => 1]);
    $product = demandProductNeeding($flour, 500, 'cups');

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 2)]);

    expect($shortages)->toBeEmpty();
    $logger->shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context): bool => $context['ingredient'] === 'Flour'
            && $context['recipe_unit'] === 'cups'
            && $context['stock_unit'] === 'lbs')
        ->once();
});

test('shortages skips a line whose recipe unit is not a known measurement unit', function () {
    $logger = Log::spy();
    $flour = Ingredient::factory()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 1]);
    $product = demandProductNeeding($flour, 500, 'handfuls');

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 2)]);

    expect($shortages)->toBeEmpty();
    $logger->shouldHaveReceived('warning')->once();
});

test('shortages treats identical legacy units as the same unit', function () {
    $lemons = Ingredient::factory()->create(['name' => 'Lemons', 'unit' => 'pieces', 'current_stock' => 5]);
    $product = demandProductNeeding($lemons, 2, 'pieces');

    $shortages = resolve(IngredientDemandCalculator::class)->shortages([new IngredientDemandItem($product, 3)]);

    expect($shortages)->toBe(['Lemons']);
});
