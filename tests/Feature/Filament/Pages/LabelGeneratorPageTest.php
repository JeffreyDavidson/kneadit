<?php

use App\Enums\Inventory\Allergen;
use App\Filament\Pages\Tools\LabelGenerator;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Staff\User;
use App\Presenters\ProductLabelPresenter;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('label generator shows preview after selecting products', function () {
    $product = Product::factory()->create();

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSet('showPreview', true);
});

test('label generator suggests a best by date counted from the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    $component = livewire(LabelGenerator::class);

    $component->assertSet('bestByDate', '2026-10-08');
});

/**
 * @param  list<array{0: string, 1: float|int, 2: string}>  $lines
 * @param  list<Allergen>  $allergens
 */
function productWithRecipeLines(array $lines, array $allergens = []): Product
{
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();

    foreach ($lines as [$name, $quantity, $unit]) {
        $recipe->inventoryIngredients()->attach(
            Ingredient::factory()->withAllergens($allergens)->create(['name' => $name]),
            ['quantity' => $quantity, 'unit' => $unit],
        );
    }

    return $product;
}

test('label generator lists ingredients by weight like the printed label', function () {
    $product = productWithRecipeLines([['Bread flour', 500, 'g'], ['Brown sugar', 2, 'lbs']]);

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSeeInOrder(['Ingredients:', 'Brown sugar, Bread flour']);
});

test('label generator shows the allergen statement from the linked ingredients', function () {
    $product = productWithRecipeLines([['Bread flour', 500, 'g']], [Allergen::Wheat]);

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSee('Contains: Wheat.');
});

test('label generator truncates the ordered ingredient list', function () {
    $product = productWithRecipeLines([
        ['Heavy ingredient number one', 900, 'g'],
        ['Heavy ingredient number two', 800, 'g'],
        ['Heavy ingredient number three', 700, 'g'],
        ['Light ingredient', 5, 'g'],
    ]);

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->set('labelSize', 'medium')
        ->call('generateLabels')
        ->assertSee('Heavy ingredient number one')
        ->assertDontSee('Light ingredient');
});

test('label generator hints that ordering is approximate when a line has no weight', function () {
    $product = productWithRecipeLines([['Bread flour', 500, 'g'], ['Whole milk', 4, 'cups']]);

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSeeHtml('<p class="ordering-note">');
});

test('label generator shows no ordering hint when every line is weighed', function () {
    $product = productWithRecipeLines([['Bread flour', 500, 'g'], ['Brown sugar', 2, 'lbs']]);

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertDontSeeHtml('<p class="ordering-note">');
});

test('label generator falls back to the recipe text ingredients when none are linked', function () {
    $product = Product::factory()->create();
    Recipe::factory()->for($product)->create([
        'ingredients' => [
            ['name' => 'Butter', 'quantity' => 1, 'unit' => 'cup'],
            ['name' => 'Sourdough starter', 'quantity' => 3, 'unit' => 'cups'],
        ],
    ]);

    livewire(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSee('Sourdough starter, Butter');
});

test('label generator eager-loads the recipe ingredients so the query count stays flat', function () {
    $queriesFor = function (int $productCount): int {
        $ids = collect(range(1, $productCount))
            ->map(fn (): int => productWithRecipeLines([['Bread flour', 500, 'g'], ['Whole milk', 4, 'cups']])->id)
            ->all();

        $component = livewire(LabelGenerator::class)
            ->set('selectedProducts', $ids)
            ->call('generateLabels');

        DB::flushQueryLog();
        DB::enableQueryLog();
        $component->instance()->getSelectedProductModels()->each(
            fn (Product $product) => ProductLabelPresenter::for($product)->ingredientNames(),
        );
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($queriesFor(3))->toBe($queriesFor(1));
});
