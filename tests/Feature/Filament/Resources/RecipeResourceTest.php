<?php

use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list recipes in the table', function () {
    $recipes = Recipe::factory()->count(3)->create();

    Livewire::test(ListRecipes::class)
        ->assertCanSeeTableRecords($recipes);
});

test('can render recipe table columns', function (string $column) {
    Recipe::factory()->create();

    Livewire::test(ListRecipes::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'prep_time_minutes']);

test('can search recipes by name', function () {
    $target = Recipe::factory()->create(['name' => 'Sourdough Starter']);
    $other = Recipe::factory()->create(['name' => 'Chocolate Cake']);

    Livewire::test(ListRecipes::class)
        ->searchTable('Sourdough')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can edit a recipe via table action', function () {
    $recipe = Recipe::factory()->create();

    Livewire::test(ListRecipes::class)
        ->callTableAction('edit', $recipe, data: [
            'name' => 'Updated Recipe',
            'prep_time_minutes' => $recipe->prep_time_minutes,
        ])
        ->assertHasNoTableActionErrors();

    expect($recipe->fresh()->name)->toBe('Updated Recipe');
});

test('edit recipe validates name is required', function () {
    $recipe = Recipe::factory()->create();

    Livewire::test(ListRecipes::class)
        ->callTableAction('edit', $recipe, data: [
            'name' => null,
            'prep_time_minutes' => $recipe->prep_time_minutes,
        ])
        ->assertHasTableActionErrors(['name' => 'required']);
});
