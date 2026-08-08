<?php

use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active products', function () {
    $active = Product::factory()->active()->create();
    Product::factory()->inactive()->create();

    $results = Product::query()->active()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($active->id);
});

test('featured scope returns only featured products', function () {
    $featured = Product::factory()->featured()->create();
    Product::factory()->create();

    $results = Product::query()->featured()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($featured->id);
});
