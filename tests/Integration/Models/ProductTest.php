<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\SeasonalItem;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    User::factory()->owner()->create();
});

test('product belongs to category', function () {
    $category = Category::factory()->create(['name' => 'Cakes']);
    $product = Product::factory()->for($category)->create(['name' => 'Chocolate Cake']);

    expect($product->category)->toBeInstanceOf(Category::class)->and($product->category->name)->toBe('Cakes');
});

test('product has recipes relationship', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    Recipe::factory()->for($product)->create(['name' => 'Sourdough Recipe']);

    expect($product->recipes)->toHaveCount(1);
});

test('product has seasonal items', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Pie']);

    SeasonalItem::factory()->for($product)->create();

    expect($product->seasonalItems)->toHaveCount(1);
});

test('is in season returns true for current seasonal products', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Pie']);

    SeasonalItem::factory()->for($product)->current()->create();

    $product->load('seasonalItems');

    expect($product->is_in_season)->toBeTrue();
});

test('is in season returns false for out of season products', function () {
    $product = Product::factory()->create(['name' => 'Pumpkin Pie']);

    SeasonalItem::factory()->for($product)->expired()->create();

    $product->load('seasonalItems');

    expect($product->is_in_season)->toBeFalse();
});

test('product with no seasonal entries is always in season', function () {
    $product = Product::factory()->create(['name' => 'Sourdough']);

    $product->load('seasonalItems');

    expect($product->is_in_season)->toBeTrue();
});

test('product margin attribute', function () {
    $product = Product::factory()->create(['name' => 'Sourdough', 'price' => 10.00, 'cost' => 4.00]);

    expect($product->margin)->toBe(60.0);
});

test('product margin is null without cost', function () {
    $product = Product::factory()->create(['name' => 'Sourdough', 'price' => 10.00]);

    expect($product->margin)->toBeNull();
});
