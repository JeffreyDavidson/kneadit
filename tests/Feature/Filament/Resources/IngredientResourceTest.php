<?php

use App\Filament\Resources\Ingredients\Pages\ListIngredients;
use App\Models\Ingredient;
use App\Models\User;
use Filament\Actions\CreateAction;
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
        ->assertHasNoActionErrors();

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
        ->assertHasActionErrors($errors);
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'unit is required' => [['unit' => null], ['unit' => 'required']],
    'current stock is required' => [['current_stock' => null], ['current_stock' => 'required']],
]);
