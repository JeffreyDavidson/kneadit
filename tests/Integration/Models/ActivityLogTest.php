<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Operations\ActivityLog;
use App\Models\Staff\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create(['name' => 'Baker Bob']);
    test()->category = Category::factory()->create(['name' => 'Bread']);
});

test('creating a product logs created activity', function () {
    $product = Product::factory()->for(testFixture('category', Category::class))->create(['name' => 'Sourdough']);

    $log = ActivityLog::query()->where('model_type', Product::class)->where('model_id', $product->id)->where('action', 'created')->first();

    expect($log)->not->toBeNull()->and($log->description)->toContain('created');
});

test('updating a product logs updated activity', function () {
    $product = Product::factory()->for(testFixture('category', Category::class))->create(['name' => 'Sourdough']);

    $product->update(['price' => 6.00]);

    $log = ActivityLog::query()->where('model_type', Product::class)->where('model_id', $product->id)->where('action', 'updated')->first();

    expect($log)->not->toBeNull();
});

test('deleting a product logs deleted activity', function () {
    $product = Product::factory()->for(testFixture('category', Category::class))->create(['name' => 'Sourdough']);
    $productId = $product->id;

    $product->delete();

    $log = ActivityLog::query()->where('model_type', Product::class)->where('model_id', $productId)->where('action', 'deleted')->first();

    expect($log)->not->toBeNull();
});

test('activity log stores user name', function () {
    actingAs(testFixture('user', User::class));

    Product::factory()->for(testFixture('category', Category::class))->create(['name' => 'Sourdough']);

    $log = ActivityLog::query()->where('action', 'created')->where('model_type', Product::class)->latest('id')->first();

    expect($log->user_name)->toBe('Baker Bob');
});

test('changes are captured in properties', function () {
    $product = Product::factory()->for(testFixture('category', Category::class))->create(['name' => 'Sourdough']);

    $product->update(['price' => 7.50]);

    $log = ActivityLog::query()->where('action', 'updated')->where('model_type', Product::class)->where('model_id', $product->id)->first();

    expect($log->properties)->not->toBeNull()->toHaveKey('changes');
});

test('system user name when not authenticated', function () {
    Product::factory()->for(testFixture('category', Category::class))->create(['name' => 'Sourdough']);

    $log = ActivityLog::query()->where('action', 'created')->where('model_type', Product::class)->latest('id')->first();

    expect($log->user_name)->toBe('System');
});
