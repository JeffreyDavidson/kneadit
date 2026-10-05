<?php

use App\Enums\Inventory\StockAdjustmentType;
use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\StockAdjustment;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list ingredients in the table', function () {
    $ingredients = Ingredient::factory()->count(3)->create();

    livewire(ListIngredients::class)
        ->assertCanSeeTableRecords($ingredients);
});

test('can create an ingredient via slide-over', function () {
    livewire(ListIngredients::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Bread Flour',
            'unit' => 'lbs',
            'current_stock' => 50.00,
            'low_stock_threshold' => 10.00,
            'cost_per_unit' => 2.50,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Ingredient::class, [
        'name' => 'Bread Flour',
        'unit' => 'lbs',
    ]);
});

test('create ingredient validates required fields', function () {
    $cases = [
        [['name' => null], ['name' => 'required']],
        [['unit' => null], ['unit' => 'required']],
        [['current_stock' => null], ['current_stock' => 'required']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListIngredients::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Test',
                'unit' => 'lbs',
                'current_stock' => 10,
                'low_stock_threshold' => 5,
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('can search ingredients by name', function () {
    $flour = Ingredient::factory()->create(['name' => 'Bread Flour']);
    $sugar = Ingredient::factory()->create(['name' => 'Brown Sugar']);

    livewire(ListIngredients::class)
        ->searchTable('Flour')
        ->assertCanSeeTableRecords(collect([$flour]))
        ->assertCanNotSeeTableRecords(collect([$sugar]));
});

test('can render ingredient table columns', function () {
    Ingredient::factory()->create();

    livewire(ListIngredients::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('current_stock')
        ->assertCanRenderTableColumn('stock_status')
        ->assertCanRenderTableColumn('cost_per_unit')
        ->assertCanRenderTableColumn('is_active');
});

test('the stock column shows 2 decimals unless that would show a small stock as 0', function (string $stock, string $expected) {
    $ingredient = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => $stock]);

    livewire(ListIngredients::class)
        ->assertTableColumnFormattedStateSet('current_stock', $expected, $ingredient);
})->with([
    'a whole amount' => ['8', '8.00 kg'],
    'an amount with many decimals' => ['9.9970', '10.00 kg'],
    'a thousandth' => ['0.003', '0.003 kg'],
]);

test('the edit form keeps the stored digits of a small stock', function () {
    $ingredient = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => '9.9970', 'low_stock_threshold' => '5']);

    livewire(ListIngredients::class)
        ->mountAction(TestAction::make('edit')->table($ingredient))
        ->assertSchemaStateSet(['current_stock' => '9.997', 'low_stock_threshold' => '5.00']);
});

test('can edit an ingredient via table action', function () {
    $ingredient = Ingredient::factory()->create(['unit' => 'lbs']);

    livewire(ListIngredients::class)
        ->callAction(TestAction::make('edit')->table($ingredient), data: [
            'name' => 'Updated Flour',
            'unit' => 'lbs',
            'current_stock' => $ingredient->current_stock,
            'low_stock_threshold' => $ingredient->low_stock_threshold,
        ])
        ->assertHasNoFormErrors();

    expect($ingredient->fresh()->name)->toBe('Updated Flour');
});

test('can archive an ingredient without deleting it', function () {
    $ingredient = Ingredient::factory()->create(['unit' => 'lbs']);

    livewire(ListIngredients::class)
        ->callAction(TestAction::make('edit')->table($ingredient), data: [
            'name' => $ingredient->name,
            'unit' => $ingredient->unit,
            'current_stock' => $ingredient->current_stock,
            'low_stock_threshold' => $ingredient->low_stock_threshold,
            'is_active' => false,
        ])
        ->assertHasNoFormErrors();

    expect($ingredient->fresh()->is_active)->toBeFalse();

    livewire(ListIngredients::class)
        ->assertCanNotSeeTableRecords(collect([$ingredient]))
        ->filterTable('is_active', '0')
        ->assertCanSeeTableRecords(collect([$ingredient]));
});

test('cannot delete an ingredient with stock adjustment history', function () {
    $ingredient = Ingredient::factory()->create();
    StockAdjustment::factory()->for($ingredient)->create();
    $user = User::factory()->owner()->create();

    expect(Gate::forUser($user)->allows('delete', $ingredient))->toBeFalse();
});

test('cannot delete an ingredient used by a recipe', function () {
    $ingredient = Ingredient::factory()->create();
    $recipe = Recipe::factory()->create();
    $recipe->inventoryIngredients()->attach($ingredient, ['quantity' => 1, 'unit' => $ingredient->unit]);
    $user = User::factory()->owner()->create();

    expect(Gate::forUser($user)->allows('delete', $ingredient))->toBeFalse();
});

test('can filter ingredients by low stock', function () {
    $lowStock = Ingredient::factory()->lowStock()->create();
    $normalStock = Ingredient::factory()->create(['current_stock' => 50]);

    livewire(ListIngredients::class)
        ->filterTable('stock_status', 'low')
        ->assertCanSeeTableRecords(collect([$lowStock]))
        ->assertCanNotSeeTableRecords(collect([$normalStock]));
});

test('can sort ingredients by name', function () {
    $butter = Ingredient::factory()->create(['name' => 'Butter']);
    $yeast = Ingredient::factory()->create(['name' => 'Yeast']);

    livewire(ListIngredients::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$butter, $yeast]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$yeast, $butter]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(IngredientResource::getGloballySearchableAttributes())
        ->toBe(['name', 'supplier']);
});

test('resource returns global search result title', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Bread Flour']);

    expect(IngredientResource::getGlobalSearchResultTitle($ingredient))
        ->toBe('Bread Flour');
});

test('resource returns global search result details', function () {
    $ingredient = Ingredient::factory()->create([
        'supplier' => 'King Arthur',
        'current_stock' => 50,
        'unit' => 'lbs',
    ]);

    $details = IngredientResource::getGlobalSearchResultDetails($ingredient);

    expect($details)
        ->toHaveKey('Supplier', 'King Arthur')
        ->toHaveKey('Stock');
});

test('recording usage or waste above the stock on hand shows an error and records nothing', function (StockAdjustmentType $type) {
    $ingredient = Ingredient::factory()->create(['name' => 'Rye Flour', 'unit' => 'kg', 'current_stock' => 2]);

    livewire(ListIngredients::class)
        ->callAction(TestAction::make('record_stock')->table($ingredient), data: ['type' => $type->value, 'quantity' => 5, 'notes' => ''])
        ->assertNotified('Not enough Rye Flour on hand');

    expect($ingredient->fresh()->current_stock)->toBe('2.0000')
        ->and(StockAdjustment::query()->where('ingredient_id', $ingredient->id)->count())->toBe(0);
})->with([
    'usage' => StockAdjustmentType::Usage,
    'waste' => StockAdjustmentType::Waste,
]);

test('recording usage within the stock on hand subtracts it', function () {
    $ingredient = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 2]);

    livewire(ListIngredients::class)
        ->callAction(TestAction::make('record_stock')->table($ingredient), data: ['type' => StockAdjustmentType::Usage->value, 'quantity' => 1.5, 'notes' => ''])
        ->assertNotNotified('Not enough '.$ingredient->name.' on hand');

    expect($ingredient->fresh()->current_stock)->toBe('0.5000');
});

test('recording a purchase for selected ingredients adds the stock with or without notes', function () {
    $ingredients = Ingredient::factory()->count(2)->create(['current_stock' => 1]);

    livewire(ListIngredients::class)
        ->selectTableRecords($ingredients)
        ->callAction(TestAction::make('record_purchase')->table()->bulk(), data: ['quantity' => 4, 'notes' => null]);

    expect($ingredients->map(fn (Ingredient $ingredient): string => $ingredient->fresh()->current_stock)->all())->toBe(['5.0000', '5.0000']);
});
