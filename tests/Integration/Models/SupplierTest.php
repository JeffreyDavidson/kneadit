<?php

use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
    expect($pivot->unit_price)->toBe(1.20);
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
