<?php

use App\Enums\Inventory\Allergen;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Presenters\ProductLabelPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns empty ingredient list and no allergen statement when product has no recipe', function () {
    $product = Product::factory()->create();
    $presenter = ProductLabelPresenter::for($product);

    expect($presenter->ingredientNames())->toBeEmpty()
        ->and($presenter->allergens())->toBeEmpty()
        ->and($presenter->allergenStatement())->toBeNull();
});

dataset('ingredient orderings', [
    'pounds outweigh a smaller number of grams' => [
        [['Flour', 500, 'g'], ['Sugar', 2, 'lbs']],
        ['Sugar', 'Flour'],
        false,
    ],
    'kilograms beat a larger number of grams' => [
        [['Butter', 900, 'g'], ['Flour', 1, 'kg']],
        ['Flour', 'Butter'],
        false,
    ],
    'ounces and grams are compared as weights' => [
        [['Salt', 20, 'g'], ['Yeast', 1, 'oz'], ['Sugar', 100, 'g']],
        ['Sugar', 'Yeast', 'Salt'],
        false,
    ],
    'equal weights keep the entry order' => [
        [['Flour', 1000, 'g'], ['Sugar', 1, 'kg'], ['Salt', 1000, 'g']],
        ['Flour', 'Sugar', 'Salt'],
        false,
    ],
    'weighed lines come before volume lines' => [
        [['Milk', 4, 'cups'], ['Flour', 500, 'g'], ['Salt', 5, 'g']],
        ['Flour', 'Salt', 'Milk'],
        true,
    ],
    'weighed lines come before count lines' => [
        [['Eggs', 12, 'each'], ['Salt', 5, 'g']],
        ['Salt', 'Eggs'],
        true,
    ],
    'a legacy unit is treated as unweighed' => [
        [['Mystery', 9, 'pinch'], ['Salt', 5, 'g']],
        ['Salt', 'Mystery'],
        true,
    ],
    'all volume lines keep the entry order' => [
        [['Salt', 1, 'tsp'], ['Flour', 4, 'cups'], ['Milk', 2, 'cups']],
        ['Salt', 'Flour', 'Milk'],
        true,
    ],
]);

test('orders linked ingredients by their weight in grams', function (array $lines, array $expectedNames, bool $hasUnweighed) {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    foreach ($lines as [$name, $quantity, $unit]) {
        $recipe->inventoryIngredients()->attach(
            Ingredient::factory()->create(['name' => $name]),
            ['quantity' => $quantity, 'unit' => $unit],
        );
    }

    $presenter = ProductLabelPresenter::for($product->fresh());

    expect($presenter->ingredientNames())->toBe($expectedNames)
        ->and($presenter->hasUnweighedIngredients())->toBe($hasUnweighed);
})->with('ingredient orderings');

test('reports no unweighed ingredients when the product has no recipe', function () {
    $presenter = ProductLabelPresenter::for(Product::factory()->create());

    expect($presenter->hasUnweighedIngredients())->toBeFalse();
});

test('derives allergen statement from the union of linked ingredient allergens', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    $flour = Ingredient::factory()->withAllergens([Allergen::Wheat])->create(['name' => 'Flour']);
    $milk = Ingredient::factory()->withAllergens([Allergen::Milk])->create(['name' => 'Milk']);
    $eggs = Ingredient::factory()->withAllergens([Allergen::Eggs])->create(['name' => 'Eggs']);
    $salt = Ingredient::factory()->create(['name' => 'Salt']);

    $recipe->inventoryIngredients()->attach([
        $flour->id => ['quantity' => 3, 'unit' => 'cups'],
        $milk->id => ['quantity' => 1, 'unit' => 'cups'],
        $eggs->id => ['quantity' => 2, 'unit' => 'each'],
        $salt->id => ['quantity' => 0.5, 'unit' => 'tsp'],
    ]);

    $presenter = ProductLabelPresenter::for($product->fresh());

    expect($presenter->allergens())
        ->toHaveCount(3)
        ->and($presenter->allergenStatement())->toBe('Contains: Eggs, Milk, Wheat.');
});

test('falls back to the recipe JSON ingredients column when no pantry ingredients are linked', function () {
    $product = Product::factory()->create();
    Recipe::factory()->for($product)->create([
        'ingredients' => [
            ['name' => 'Butter', 'quantity' => 1, 'unit' => 'cup'],
            ['name' => 'Sourdough starter', 'quantity' => 3, 'unit' => 'cups'],
            ['name' => 'Sea salt', 'quantity' => 0.25, 'unit' => 'tsp'],
        ],
    ]);

    $presenter = ProductLabelPresenter::for($product->fresh());

    expect($presenter->ingredientNames())->toBe(['Sourdough starter', 'Butter', 'Sea salt'])
        ->and($presenter->allergenStatement())->toBeNull();
});

test('de-duplicates allergens even when multiple ingredients share one', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    $butter = Ingredient::factory()->withAllergens([Allergen::Milk])->create(['name' => 'Butter']);
    $cream = Ingredient::factory()->withAllergens([Allergen::Milk])->create(['name' => 'Cream']);

    $recipe->inventoryIngredients()->attach([
        $butter->id => ['quantity' => 1, 'unit' => 'cup'],
        $cream->id => ['quantity' => 2, 'unit' => 'cups'],
    ]);

    $presenter = ProductLabelPresenter::for($product->fresh());

    expect($presenter->allergens())->toHaveCount(1)
        ->and($presenter->allergenStatement())->toBe('Contains: Milk.');
});
