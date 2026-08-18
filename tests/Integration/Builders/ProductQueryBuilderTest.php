<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\SeasonalItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active products', function () {
    Product::factory()->active()->create();
    Product::factory()->inactive()->create();

    expect(Product::query()->active()->count())->toBe(1);
});

test('featured scope returns only featured products', function () {
    Product::factory()->active()->featured()->create();
    Product::factory()->active()->create();

    expect(Product::query()->featured()->count())->toBe(1);
});

test('inSeason returns products without seasonal items', function () {
    $product = Product::factory()->create();

    $results = Product::query()->inSeason()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($product->id);
});

test('inSeason returns products with current seasonal items', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->current()->recycle($product)->create();

    $results = Product::query()->inSeason()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($product->id);
});

test('inSeason excludes products with only expired seasonal items', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->expired()->recycle($product)->create();

    $results = Product::query()->inSeason()->get();

    expect($results)->toBeEmpty();
});

test('inSeason excludes products with only upcoming seasonal items', function () {
    $product = Product::factory()->create();
    SeasonalItem::factory()->upcoming()->recycle($product)->create();

    $results = Product::query()->inSeason()->get();

    expect($results)->toBeEmpty();
});
