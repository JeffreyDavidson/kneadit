<?php

use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Models\Inventory\Ingredient;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

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
        ->assertCanRenderTableColumn('cost_per_unit');
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
    expect(App\Filament\Resources\Ingredients\IngredientResource::getGloballySearchableAttributes())
        ->toBe(['name', 'supplier']);
});

test('resource returns global search result title', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Bread Flour']);

    expect(App\Filament\Resources\Ingredients\IngredientResource::getGlobalSearchResultTitle($ingredient))
        ->toBe('Bread Flour');
});

test('resource returns global search result details', function () {
    $ingredient = Ingredient::factory()->create([
        'supplier' => 'King Arthur',
        'current_stock' => 50,
        'unit' => 'lbs',
    ]);

    $details = App\Filament\Resources\Ingredients\IngredientResource::getGlobalSearchResultDetails($ingredient);

    expect($details)
        ->toHaveKey('Supplier', 'King Arthur')
        ->toHaveKey('Stock');
});
