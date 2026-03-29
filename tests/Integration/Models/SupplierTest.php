<?php

use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

test('supplier has ingredients relationship', function () {
    $supplier = Supplier::query()->create(['name' => 'Flour Co', 'is_active' => true]);
    $ingredient = Ingredient::query()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100, 'low_stock_threshold' => 10, 'cost_per_unit' => 1.50]);

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    expect($supplier->fresh()->ingredients)->toHaveCount(1)->and($supplier->fresh()->ingredients->first()->name)->toBe('Flour');
});

test('ingredients pivot has unit price', function () {
    $supplier = Supplier::query()->create(['name' => 'Flour Co', 'is_active' => true]);
    $ingredient = Ingredient::query()->create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100, 'low_stock_threshold' => 10, 'cost_per_unit' => 1.50]);

    $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

    $pivot = $supplier->fresh()->ingredients->first()->pivot;
    expect($pivot->unit_price)->toBe(1.20);
});

test('is active is cast to boolean', function () {
    $supplier = Supplier::query()->create(['name' => 'Flour Co', 'is_active' => true]);

    expect($supplier->is_active)->toBeBool()->toBeTrue();
});

test('supplier can be deactivated', function () {
    $supplier = Supplier::query()->create(['name' => 'Flour Co', 'is_active' => true]);
    $supplier->update(['is_active' => false]);

    expect($supplier->fresh()->is_active)->toBeFalse();
});
