<?php

use App\Enums\Inventory\Allergen;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

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

test('label page orders ingredients by weight and warns when some lines are not weighed', function () {
    actingAs(User::factory()->owner()->create());
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    $recipe->inventoryIngredients()->attach([
        Ingredient::factory()->create(['name' => 'Milk'])->id => ['quantity' => 2, 'unit' => 'cups'],
        Ingredient::factory()->create(['name' => 'Flour'])->id => ['quantity' => 500, 'unit' => 'g'],
        Ingredient::factory()->create(['name' => 'Sugar'])->id => ['quantity' => 2, 'unit' => 'lbs'],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.products.label', $product, false));

    $response->assertOk()
        ->assertSee('Sugar, Flour, Milk.')
        ->assertSee('Some ingredients use volume or count units, so their position on the label is approximate.');
});

test('label page shows no ordering warning when every line is weighed', function () {
    actingAs(User::factory()->owner()->create());
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    $recipe->inventoryIngredients()->attach([
        Ingredient::factory()->create(['name' => 'Flour'])->id => ['quantity' => 500, 'unit' => 'g'],
        Ingredient::factory()->create(['name' => 'Sugar'])->id => ['quantity' => 2, 'unit' => 'lbs'],
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.products.label', $product, false));

    $response->assertOk()
        ->assertSee('Sugar, Flour.')
        ->assertDontSee('position on the label is approximate');
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
