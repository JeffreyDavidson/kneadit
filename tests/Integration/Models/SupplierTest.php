<?php

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Supplier;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('supplier has ingredients relationship', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    expect($supplier->refresh()->ingredients)->toHaveCount(1)->and($supplier->refresh()->ingredients->firstOrFail()->name)->toBe('Flour');
});

test('ingredients pivot has unit price', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    $pivot = $supplier->refresh()->ingredients->firstOrFail()->pivot;
    expect($pivot?->getAttribute('unit_price'))->toBe(1.20);
});

test('is active is cast to boolean', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);

    expect($supplier->is_active)->toBeBool()->toBeTrue();
});

test('supplier can be deactivated', function () {
    $supplier = Supplier::factory()->create(['name' => 'Flour Co']);
    $supplier->update(['is_active' => false]);

    expect($supplier->refresh()->is_active)->toBeFalse();
});
