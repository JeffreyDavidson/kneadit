<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Inventory\MeasurementUnit;
use App\Enums\Inventory\UnitDimension;
use App\Enums\Orders\OrderStatus;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Filament\Resources\Recipes\RecipeResource;
use App\Filament\Resources\Recipes\Schemas\RecipeForm;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\RecipeIngredient;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

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
        'ingredientLines' => [],
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
    expect(RecipeResource::getGloballySearchableAttributes())
        ->toBe(['name']);
});

test('resource returns global search result title', function () {
    $recipe = Recipe::factory()->create(['name' => 'Sourdough Bread']);

    expect(RecipeResource::getGlobalSearchResultTitle($recipe))
        ->toBe('Sourdough Bread');
});

test('resource returns global search result details', function () {
    $recipe = Recipe::factory()->create(['prep_time_minutes' => 120]);

    $details = RecipeResource::getGlobalSearchResultDetails($recipe);

    expect($details)
        ->toHaveKey('Product')
        ->toHaveKey('Prep Time', '120 min');
});

test('global search eloquent query eager loads product', function () {
    $query = RecipeResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('product');
});

test('linked ingredient lines reject a unit from another dimension', function (string $stockUnit, string $recipeUnit) {
    $recipe = Recipe::factory()->create();
    $ingredient = Ingredient::factory()->create(['unit' => $stockUnit]);

    livewire(ListRecipes::class)
        ->callAction(TestAction::make('edit')->table($recipe), data: [
            'name' => $recipe->name,
            'prep_time_minutes' => $recipe->prep_time_minutes,
            'ingredientLines' => [
                ['ingredient_id' => $ingredient->id, 'quantity' => 1, 'unit' => $recipeUnit],
            ],
        ])
        ->assertHasFormErrors(['ingredientLines.0.unit']);

    expect($recipe->inventoryIngredients()->count())->toBe(0);
})->with([
    'volume unit for an ingredient stocked in kg' => ['kg', 'cups'],
    'mass unit for an ingredient stocked in ml' => ['ml', 'g'],
    'count unit for an ingredient stocked in lbs' => ['lbs', 'each'],
]);

test('the unit options follow the dimension of the selected ingredient', function () {
    $ingredient = Ingredient::factory()->create(['unit' => 'kg']);

    expect(RecipeForm::unitOptions($ingredient->id))
        ->toBe(MeasurementUnit::options(UnitDimension::Mass))
        ->and(RecipeForm::unitOptions(null))->toBe(MeasurementUnit::options());
});

test('saving a recipe with linked ingredient lines writes the pivot rows', function () {
    $flour = Ingredient::factory()->create(['unit' => 'kg']);
    $butter = Ingredient::factory()->create(['unit' => 'lbs']);

    livewire(ListRecipes::class)
        ->callAction('create', data: [
            'name' => 'Shortbread',
            'prep_time_minutes' => 30,
            'instructions' => 'Mix and bake.',
            'ingredients' => [],
            'ingredientLines' => [
                ['ingredient_id' => $flour->id, 'quantity' => 500, 'unit' => 'g'],
                ['ingredient_id' => $butter->id, 'quantity' => 0.25, 'unit' => 'lbs'],
            ],
        ])
        ->assertHasNoFormErrors();

    $recipe = Recipe::query()->where('name', 'Shortbread')->sole();

    expect(Ingredient::query()->count())->toBe(2)
        ->and($recipe->ingredientLines)->toHaveCount(2)
        ->and($recipe->inventoryIngredients->pluck('pivot.quantity', 'id')->map(fn ($quantity): float => (float) $quantity)->all())
        ->toBe([$flour->id => 500.0, $butter->id => 0.25])
        ->and($recipe->inventoryIngredients->pluck('pivot.unit', 'id')->all())
        ->toBe([$flour->id => 'g', $butter->id => 'lbs']);
});

test('editing a recipe shows the saved quantity and unit of each linked line', function () {
    $recipe = Recipe::factory()->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg']);
    RecipeIngredient::factory()->for($recipe)->for($flour)->create(['quantity' => 500, 'unit' => 'g']);

    $component = livewire(ListRecipes::class)
        ->mountAction(TestAction::make('edit')->table($recipe));

    $lines = array_values($component->get('mountedActions.0.data.ingredientLines'));

    expect($lines)->toHaveCount(1)
        ->and((int) $lines[0]['ingredient_id'])->toBe($flour->id)
        ->and((float) $lines[0]['quantity'])->toBe(500.0)
        ->and($lines[0]['unit'])->toBe('g');
});

test('editing a recipe updates a changed line, detaches a removed one and keeps the rest', function () {
    $recipe = Recipe::factory()->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg']);
    $butter = Ingredient::factory()->create(['unit' => 'lbs']);
    $flourLine = RecipeIngredient::factory()->for($recipe)->for($flour)->create(['quantity' => 500, 'unit' => 'g']);
    RecipeIngredient::factory()->for($recipe)->for($butter)->create(['quantity' => 1, 'unit' => 'lbs']);

    livewire(ListRecipes::class)
        ->mountAction(TestAction::make('edit')->table($recipe))
        ->set('mountedActions.0.data.ingredientLines', [
            "record-{$flourLine->id}" => ['ingredient_id' => $flour->id, 'quantity' => 750, 'unit' => 'g'],
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $recipe->refresh();

    expect($recipe->ingredientLines)->toHaveCount(1)
        ->and($recipe->ingredientLines->first())
        ->id->toBe($flourLine->id)
        ->ingredient_id->toBe($flour->id)
        ->unit->toBe('g')
        ->and((float) $recipe->ingredientLines->first()->quantity)->toBe(750.0)
        ->and($recipe->inventoryIngredients()->count())->toBe(1);
});

test('the same ingredient cannot be linked twice on one recipe', function () {
    $flour = Ingredient::factory()->create(['unit' => 'kg']);

    livewire(ListRecipes::class)
        ->callAction('create', data: [
            'name' => 'Shortbread',
            'prep_time_minutes' => 30,
            'instructions' => 'Mix and bake.',
            'ingredients' => [],
            'ingredientLines' => [
                ['ingredient_id' => $flour->id, 'quantity' => 500, 'unit' => 'g'],
                ['ingredient_id' => $flour->id, 'quantity' => 1, 'unit' => 'kg'],
            ],
        ])
        ->assertHasFormErrors(['ingredientLines.0.ingredient_id', 'ingredientLines.1.ingredient_id']);

    expect(Recipe::query()->count())->toBe(0);
});

test('a recipe saved through the form deducts stock when its order moves to baking', function () {
    $product = Product::factory()->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 10]);

    livewire(ListRecipes::class)
        ->callAction('create', data: [
            'product_id' => $product->id,
            'name' => 'Boule',
            'prep_time_minutes' => 30,
            'instructions' => 'Mix and bake.',
            'ingredients' => [],
            'ingredientLines' => [
                ['ingredient_id' => $flour->id, 'quantity' => 500, 'unit' => 'g'],
            ],
        ])
        ->assertHasNoFormErrors();

    $order = Order::factory()->confirmed()->create();
    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 4]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Baking);

    expect($flour->fresh()->current_stock)->toBe('8.0000');
});
