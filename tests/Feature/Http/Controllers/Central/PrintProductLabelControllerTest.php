<?php

use App\Enums\Inventory\Allergen;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('label page renders the product name, ingredients, and allergen statement', function () {
    actingAs(User::factory()->owner()->create());
    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);
    $recipe = Recipe::factory()->for($product)->create();

    $flour = Ingredient::factory()->withAllergens([Allergen::Wheat])->create(['name' => 'Bread flour']);
    $salt = Ingredient::factory()->create(['name' => 'Sea salt']);

    $recipe->inventoryIngredients()->attach([
        $flour->id => ['quantity' => 4, 'unit' => 'cups'],
        $salt->id => ['quantity' => 0.5, 'unit' => 'tsp'],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.products.label', $product, false));

    $response->assertOk()
        ->assertSee('Sourdough Loaf')
        ->assertSee('Bread flour, Sea salt.')
        ->assertSee('Contains: Wheat.')
        ->assertSee('Made in a home kitchen');
});

test('label page shows a helpful message when no recipe is linked', function () {
    actingAs(User::factory()->owner()->create());
    $product = Product::factory()->create(['name' => 'Mystery Loaf']);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.products.label', $product, false));

    $response->assertOk()
        ->assertSee('Mystery Loaf')
        ->assertSee('No recipe on file');
});

test('label page requires authentication', function () {
    $product = Product::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.products.label', $product, false));

    $response->assertRedirect();
});
