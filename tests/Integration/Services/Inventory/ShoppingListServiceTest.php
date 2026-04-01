<?php

use App\Models\Inventory\Ingredient;
use App\Services\Inventory\ShoppingListService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('generates shopping list with low stock ingredients', function () {
    Ingredient::factory()->lowStock()->create(['name' => 'Flour']);
    Ingredient::factory()->create(['current_stock' => 100, 'low_stock_threshold' => 5]);

    $service = new ShoppingListService;
    $result = $service->generate();

    expect($result)->toBeArray()->not->toBeEmpty();
});

test('returns empty list when all stock is sufficient', function () {
    Ingredient::factory()->create(['current_stock' => 100, 'low_stock_threshold' => 5]);

    $service = new ShoppingListService;
    $result = $service->generate();

    expect($result)->toBeArray()->toBeEmpty();
});
