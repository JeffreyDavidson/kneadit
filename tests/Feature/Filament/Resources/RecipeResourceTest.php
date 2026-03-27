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
