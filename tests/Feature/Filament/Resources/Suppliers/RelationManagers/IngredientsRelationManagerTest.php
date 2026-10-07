<?php

use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\RelationManagers\IngredientsRelationManager;
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

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

    $component = livewire(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ]);

    $component->assertSuccessful();
    $component->assertCanSeeTableRecords($ingredients);
});

test('relation manager renders for a supplier with no ingredients', function () {
    $supplier = Supplier::factory()->create();

    livewire(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->assertSuccessful();
});

test('attaching an ingredient stores the price and minimum order as cents', function () {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();

    livewire(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->callAction(TestAction::make('attach')->table(), data: [
            'recordId' => $ingredient->id,
            'unit_price' => 1.5,
            'minimum_order' => 25,
            'lead_time_days' => 3,
        ])
        ->assertHasNoFormErrors();

    $row = DB::table('ingredient_supplier')->where('supplier_id', $supplier->id)->first();
    expect($row->unit_price)->toBe(150)
        ->and($row->minimum_order)->toBe(2500);
});

test('the table shows the price and minimum order in dollars', function () {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.5, 'minimum_order' => 25]);

    livewire(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->assertSeeText('$1.50')
        ->assertSeeText('$25.00');
});

test('editing shows the stored price in dollars and saves it back as cents', function () {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.5, 'minimum_order' => 25]);

    livewire(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->mountAction(TestAction::make('edit')->table($ingredient))
        ->assertSet('mountedActions.0.data.unit_price', 1.5)
        ->assertSet('mountedActions.0.data.minimum_order', 25.0);

    livewire(IngredientsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => ListSuppliers::class,
    ])
        ->callAction(TestAction::make('edit')->table($ingredient), data: [
            'unit_price' => 2.75,
            'minimum_order' => 30,
        ])
        ->assertHasNoFormErrors();

    $row = DB::table('ingredient_supplier')->where('supplier_id', $supplier->id)->first();
    expect($row->unit_price)->toBe(275)
        ->and($row->minimum_order)->toBe(3000);
});
