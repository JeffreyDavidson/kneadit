<?php

use App\Models\Category;
use App\Models\LoyaltyReward;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }
    User::query()->create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
});

test('reward type label for percentage discount', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => '10% Off',
        'points_required' => 100,
        'reward_type' => 'percentage_discount',
        'reward_value' => 10.00,
        'is_active' => true,
    ]);

    expect($reward->reward_type_label)->toBe('10.00% Off');
});

test('reward type label for fixed discount', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => '$5 Off',
        'points_required' => 50,
        'reward_type' => 'fixed_discount',
        'reward_value' => 5.00,
        'is_active' => true,
    ]);

    expect($reward->reward_type_label)->toBe('$5.00 Off');
});

test('reward type label for free product', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::query()->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

    $reward = LoyaltyReward::query()->create([
        'name' => 'Free Sourdough',
        'points_required' => 200,
        'reward_type' => 'free_product',
        'reward_value' => 0,
        'product_id' => $product->id,
        'is_active' => true,
    ]);

    expect($reward->reward_type_label)->toBe('Free Sourdough');
});

test('reward belongs to product', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
    $product = Product::query()->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

    $reward = LoyaltyReward::query()->create([
        'name' => 'Free Sourdough',
        'points_required' => 200,
        'reward_type' => 'free_product',
        'reward_value' => 0,
        'product_id' => $product->id,
        'is_active' => true,
    ]);

    expect($reward->product)->toBeInstanceOf(Product::class);
});

test('is active is cast to boolean', function () {
    $reward = LoyaltyReward::query()->create([
        'name' => 'Reward',
        'points_required' => 100,
        'reward_type' => 'percentage_discount',
        'reward_value' => 10,
        'is_active' => true,
    ]);

    expect($reward->is_active)->toBeBool();
});
