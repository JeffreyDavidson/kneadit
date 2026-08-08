<?php

use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\RelationManagers\IngredientsRelationManager;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('relation manager lists ingredients attached to the supplier', function () {
    $supplier = Supplier::factory()->create();
    $ingredients = Ingredient::factory()->count(3)->create();
    $supplier->ingredients()->attach(
        $ingredients->pluck('id')->all(),
        ['unit_price' => 1.50],
    );

    Livewire::test(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords($ingredients);
});

test('relation manager renders for a supplier with no ingredients', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->assertSuccessful();
});
