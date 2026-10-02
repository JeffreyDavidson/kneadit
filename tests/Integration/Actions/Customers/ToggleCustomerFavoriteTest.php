<?php

use App\Actions\Customers\ToggleCustomerFavorite;
use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it adds a favorite when none exists', function () {
    $product = Product::factory()->create();

    $result = resolve(ToggleCustomerFavorite::class)('test@example.com', $product->id);

    expect($result)->toBeTrue();
});

test('it removes a favorite when one exists', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->create([
        'customer_email' => 'test@example.com',
        'product_id' => $product->id,
    ]);

    $result = resolve(ToggleCustomerFavorite::class)('test@example.com', $product->id);

    expect($result)->toBeFalse();
});

test('it removes the favorite when the email differs only by case', function () {
    $product = Product::factory()->create();
    CustomerFavorite::factory()->create([
        'customer_email' => 'test@example.com',
        'product_id' => $product->id,
    ]);

    $result = resolve(ToggleCustomerFavorite::class)('Test@Example.com', $product->id);

    expect($result)->toBeFalse()
        ->and(CustomerFavorite::query()->count())->toBe(0);
});

test('it stores new favorites under the lowercased email', function () {
    $product = Product::factory()->create();

    resolve(ToggleCustomerFavorite::class)('Test@Example.com', $product->id);

    expect(CustomerFavorite::query()->value('customer_email'))->toBe('test@example.com');
});
