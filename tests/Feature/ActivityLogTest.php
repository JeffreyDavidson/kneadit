<?php

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    setUpTenantTest();
    $this->user = User::create(['name' => 'Baker Bob', 'email' => 'bob@test.com', 'password' => bcrypt('password')]);
    $this->category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
});

test('creating a product logs created activity', function () {
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

    $log = ActivityLog::where('model_type', Product::class)->where('model_id', $product->id)->where('action', 'created')->first();

    expect($log)->not->toBeNull();
    expect($log->description)->toContain('created');
});

test('updating a product logs updated activity', function () {
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

    $product->update(['price' => 6.00]);

    $log = ActivityLog::where('model_type', Product::class)->where('model_id', $product->id)->where('action', 'updated')->first();

    expect($log)->not->toBeNull();
});

test('deleting a product logs deleted activity', function () {
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);
    $productId = $product->id;

    $product->delete();

    $log = ActivityLog::where('model_type', Product::class)->where('model_id', $productId)->where('action', 'deleted')->first();

    expect($log)->not->toBeNull();
});

test('activity log stores user name', function () {
    actingAs($this->user);

    Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

    $log = ActivityLog::where('action', 'created')->where('model_type', Product::class)->latest('id')->first();

    expect($log->user_name)->toBe('Baker Bob');
});

test('changes are captured in properties', function () {
    $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

    $product->update(['price' => 7.50]);

    $log = ActivityLog::where('action', 'updated')->where('model_type', Product::class)->where('model_id', $product->id)->first();

    expect($log->properties)->not->toBeNull();
    expect($log->properties)->toHaveKey('changes');
});

test('system user name when not authenticated', function () {
    Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

    $log = ActivityLog::where('action', 'created')->where('model_type', Product::class)->latest('id')->first();

    expect($log->user_name)->toBe('System');
});
