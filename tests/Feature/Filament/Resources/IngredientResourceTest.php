<?php

use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Models\Ingredient;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list ingredients in the table', function () {
    $ingredients = Ingredient::factory()->count(3)->create();

    Livewire::test(ListIngredients::class)
        ->assertCanSeeTableRecords($ingredients);
});

test('can create an ingredient via slide-over', function () {
    Livewire::test(ListIngredients::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Bread Flour',
            'unit' => 'lbs',
            'current_stock' => 50.00,
            'low_stock_threshold' => 10.00,
            'cost_per_unit' => 2.50,
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Ingredient::class, [
        'name' => 'Bread Flour',
        'unit' => 'lbs',
    ]);
});

test('create ingredient validates required fields', function (array $data, array $errors) {
    Livewire::test(ListIngredients::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Test',
            'unit' => 'lbs',
            'current_stock' => 10,
            'low_stock_threshold' => 5,
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'unit is required' => [['unit' => null], ['unit' => 'required']],
    'current stock is required' => [['current_stock' => null], ['current_stock' => 'required']],
]);

test('can search ingredients by name', function () {
    $flour = Ingredient::factory()->create(['name' => 'Bread Flour']);
    $sugar = Ingredient::factory()->create(['name' => 'Brown Sugar']);

    Livewire::test(ListIngredients::class)
        ->searchTable('Flour')
        ->assertCanSeeTableRecords(collect([$flour]))
        ->assertCanNotSeeTableRecords(collect([$sugar]));
});

test('can render ingredient table columns', function (string $column) {
    Ingredient::factory()->create();

    Livewire::test(ListIngredients::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'current_stock', 'stock_status', 'cost_per_unit']);

test('can edit an ingredient via table action', function () {
    $ingredient = Ingredient::factory()->create(['unit' => 'lbs']);

    Livewire::test(ListIngredients::class)
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

    Livewire::test(ListIngredients::class)
        ->filterTable('stock_status', 'low')
        ->assertCanSeeTableRecords(collect([$lowStock]))
        ->assertCanNotSeeTableRecords(collect([$normalStock]));
});

test('can sort ingredients by name', function () {
    $butter = Ingredient::factory()->create(['name' => 'Butter']);
    $yeast = Ingredient::factory()->create(['name' => 'Yeast']);

    Livewire::test(ListIngredients::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$butter, $yeast]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$yeast, $butter]), inOrder: true);
});
