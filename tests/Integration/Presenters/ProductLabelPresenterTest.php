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

test('orders ingredients by quantity descending from the recipe pivot', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    $flour = Ingredient::factory()->create(['name' => 'Flour']);
    $salt = Ingredient::factory()->create(['name' => 'Salt']);
    $sugar = Ingredient::factory()->create(['name' => 'Sugar']);

    $recipe->inventoryIngredients()->attach([
        $flour->id => ['quantity' => 4.00, 'unit' => 'cups'],
        $salt->id => ['quantity' => 0.5, 'unit' => 'tsp'],
        $sugar->id => ['quantity' => 1.5, 'unit' => 'cups'],
    ]);

    $presenter = ProductLabelPresenter::for($product->refresh());

    expect($presenter->ingredientNames())->toBe(['Flour', 'Sugar', 'Salt']);
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

    $presenter = ProductLabelPresenter::for($product->refresh());

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

    $presenter = ProductLabelPresenter::for($product->refresh());

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

    $presenter = ProductLabelPresenter::for($product->refresh());

    expect($presenter->allergens())->toHaveCount(1)
        ->and($presenter->allergenStatement())->toBe('Contains: Milk.');
});
