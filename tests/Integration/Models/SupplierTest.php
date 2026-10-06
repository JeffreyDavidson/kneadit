<?php

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use App\Models\Staff\User;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('supplier has ingredients relationship', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    expect($supplier->fresh()->ingredients)->toHaveCount(1)->and($supplier->fresh()->ingredients->first()->name)->toBe('Flour');
});

test('ingredients pivot has unit price', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    $pivot = $supplier->fresh()->ingredients->first()->pivot;
    expect($pivot->unit_price)->toBeInstanceOf(Money::class)
        ->and($pivot->unit_price->cents())->toBe(120)
        ->and($pivot->unit_price->dollars())->toBe(1.20);
});

test('ingredients pivot stores unit price and minimum order as integer cents', function () {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20, 'minimum_order' => 25.50]);

    $row = DB::table('ingredient_supplier')->where('supplier_id', $supplier->id)->first();
    expect($row->unit_price)->toBe(120)
        ->and($row->minimum_order)->toBe(2550)
        ->and($supplier->ingredients()->first()->pivot->minimum_order->dollars())->toBe(25.5);
});

test('updating the pivot converts dollars to cents', function () {
    $supplier = Supplier::factory()->create();
    $ingredient = Ingredient::factory()->create();
    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    $supplier->ingredients()->first()->pivot->update(['unit_price' => 2.75]);

    expect(DB::table('ingredient_supplier')->where('supplier_id', $supplier->id)->value('unit_price'))->toBe(275);
});

test('is active is cast to boolean', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);

    expect($supplier->is_active)->toBeBool()->toBeTrue();
});

test('supplier can be deactivated', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);
    $supplier->update(['is_active' => false]);

    expect($supplier->fresh()->is_active)->toBeFalse();
});
