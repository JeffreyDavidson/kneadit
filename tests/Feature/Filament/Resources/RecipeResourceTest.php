<?php

use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Models\Inventory\Recipe;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list recipes in the table', function () {
    $recipes = Recipe::factory()->count(3)->create();

    livewire(ListRecipes::class)
        ->assertCanSeeTableRecords($recipes);
});

test('can render recipe table columns', function () {
    Recipe::factory()->create();

    livewire(ListRecipes::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('prep_time_minutes');
});

test('can search recipes by name', function () {
    $target = Recipe::factory()->create(['name' => 'Sourdough Starter']);
    $other = Recipe::factory()->create(['name' => 'Chocolate Cake']);

    livewire(ListRecipes::class)
        ->searchTable('Sourdough')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a recipe via table action', function () {
    $recipe = Recipe::factory()->create();

    livewire(ListRecipes::class)
        ->callAction(TestAction::make('edit')->table($recipe), data: [
            'name' => 'Updated Recipe',
            'prep_time_minutes' => $recipe->prep_time_minutes,
        ])
        ->assertHasNoFormErrors();

    expect($recipe->fresh()->name)->toBe('Updated Recipe');
});

test('can create a recipe with ingredients', function () {
    $component = livewire(ListRecipes::class)
        ->mountAction('create')
        ->fillForm([
            'name' => 'Sourdough Bread',
            'prep_time_minutes' => 120,
            'instructions' => 'Mix, knead, proof, bake.',
        ]);

    // Get the default ingredient item key and fill it
    $state = $component->get('mountedActions.0.data.ingredients');
    $keys = array_keys($state);
    $component->fillForm([
        'ingredients' => [
            $keys[0] => ['name' => 'Bread Flour', 'quantity' => '500', 'unit' => 'g'],
        ],
        'inventoryIngredients' => [],
    ]);

    $component->callMountedAction()
        ->assertHasNoFormErrors();

    $recipe = Recipe::query()->first();
    expect($recipe)
        ->name->toBe('Sourdough Bread')
        ->prep_time_minutes->toBe(120)
        ->ingredients->toHaveCount(1);
});

test('edit recipe validates name is required', function () {
    $recipe = Recipe::factory()->create();

    livewire(ListRecipes::class)
        ->callAction(TestAction::make('edit')->table($recipe), data: [
            'name' => null,
            'prep_time_minutes' => $recipe->prep_time_minutes,
        ])
        ->assertHasFormErrors(['name' => 'required']);
});

test('can sort recipes by name', function () {
    $alpha = Recipe::factory()->create(['name' => 'Alpha Bread']);
    $zeta = Recipe::factory()->create(['name' => 'Zeta Cake']);

    livewire(ListRecipes::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alpha, $zeta]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zeta, $alpha]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Recipes\RecipeResource::getGloballySearchableAttributes())
        ->toBe(['name']);
});

test('resource returns global search result title', function () {
    $recipe = Recipe::factory()->create(['name' => 'Sourdough Bread']);

    expect(App\Filament\Resources\Recipes\RecipeResource::getGlobalSearchResultTitle($recipe))
        ->toBe('Sourdough Bread');
});

test('resource returns global search result details', function () {
    $recipe = Recipe::factory()->create(['prep_time_minutes' => 120]);

    $details = App\Filament\Resources\Recipes\RecipeResource::getGlobalSearchResultDetails($recipe);

    expect($details)
        ->toHaveKey('Product')
        ->toHaveKey('Prep Time', '120 min');
});

test('global search eloquent query eager loads product', function () {
    $query = App\Filament\Resources\Recipes\RecipeResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('product');
});
