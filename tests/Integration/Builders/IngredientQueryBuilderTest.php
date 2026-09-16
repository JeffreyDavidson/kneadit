<?php

use App\Models\Inventory\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('lowStock returns ingredients at or below their threshold including depleted stock', function () {
    $belowThreshold = Ingredient::factory()->create([
        'current_stock' => 4,
        'low_stock_threshold' => 5,
    ]);
    $atThreshold = Ingredient::factory()->create([
        'current_stock' => 5,
        'low_stock_threshold' => 5,
    ]);
    $depleted = Ingredient::factory()->create([
        'current_stock' => 0,
        'low_stock_threshold' => -1,
    ]);
    Ingredient::factory()->create([
        'current_stock' => 6,
        'low_stock_threshold' => 5,
    ]);

    $ingredients = Ingredient::query()->lowStock()->get();

    expect($ingredients->modelKeys())->toEqualCanonicalizing([
        $belowThreshold->id,
        $atThreshold->id,
        $depleted->id,
    ]);
});
