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
