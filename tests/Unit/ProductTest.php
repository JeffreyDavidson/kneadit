<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\SeasonalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

test('product belongs to category', function () {
    $category = Category::create(['name' => 'Cakes', 'slug' => 'cakes']);
    $product = Product::create(['name' => 'Chocolate Cake', 'slug' => 'chocolate-cake', 'price' => 25.00, 'category_id' => $category->id, 'is_active' => true]);

    expect($product->category)->toBeInstanceOf(Category::class);
    expect($product->category->name)->toBe('Cakes');
});

test('product has recipes relationship', function () {
    $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

    Recipe::create(['product_id' => $product->id, 'name' => 'Sourdough Recipe', 'instructions' => 'Mix and bake', 'ingredients' => json_encode(['flour', 'water', 'salt'])]);

    expect($product->recipes)->toHaveCount(1);
});

test('product has seasonal items', function () {
    $category = Category::create(['name' => 'Pies', 'slug' => 'pies']);
    $product = Product::create(['name' => 'Pumpkin Pie', 'slug' => 'pumpkin-pie', 'price' => 15.00, 'category_id' => $category->id, 'is_active' => true]);

    SeasonalItem::create([
        'product_id' => $product->id,
        'available_from' => Date::now()->subMonth(),
        'available_until' => Date::now()->addMonth(),
    ]);

    expect($product->seasonalItems)->toHaveCount(1);
});

test('is in season returns true for current seasonal products', function () {
    $category = Category::create(['name' => 'Pies', 'slug' => 'pies']);
    $product = Product::create(['name' => 'Pumpkin Pie', 'slug' => 'pumpkin-pie', 'price' => 15.00, 'category_id' => $category->id, 'is_active' => true]);

    SeasonalItem::create([
        'product_id' => $product->id,
        'available_from' => Date::now()->subMonth(),
        'available_until' => Date::now()->addMonth(),
    ]);

    $product->load('seasonalItems');

    expect($product->isInSeason())->toBeTrue();
});

test('is in season returns false for out of season products', function () {
    $category = Category::create(['name' => 'Pies', 'slug' => 'pies']);
    $product = Product::create(['name' => 'Pumpkin Pie', 'slug' => 'pumpkin-pie', 'price' => 15.00, 'category_id' => $category->id, 'is_active' => true]);

    SeasonalItem::create([
        'product_id' => $product->id,
        'available_from' => Date::now()->subMonths(6),
        'available_until' => Date::now()->subMonths(3),
    ]);

    $product->load('seasonalItems');

    expect($product->isInSeason())->toBeFalse();
});

test('product with no seasonal entries is always in season', function () {
    $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

    $product->load('seasonalItems');

    expect($product->isInSeason())->toBeTrue();
});

test('product margin attribute', function () {
    $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 10.00, 'cost' => 4.00, 'category_id' => $category->id, 'is_active' => true]);

    expect($product->margin)->toBe(60.0);
});

test('product margin is null without cost', function () {
    $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 10.00, 'category_id' => $category->id, 'is_active' => true]);

    expect($product->margin)->toBeNull();
});
