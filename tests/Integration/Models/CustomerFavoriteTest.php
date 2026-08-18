<?php

use App\Models\Customers\CustomerFavorite;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('product relationship returns the associated product', function () {
    $product = Product::factory()->create();
    $favorite = CustomerFavorite::factory()->create([
        'product_id' => $product->id,
    ]);

    expect($favorite->product->id)->toBe($product->id);
});

test('for customer scope filters by email', function () {
    $favorite = CustomerFavorite::factory()->create([
        'customer_email' => 'jane@example.com',
    ]);
    CustomerFavorite::factory()->create([
        'customer_email' => 'other@example.com',
    ]);

    $results = CustomerFavorite::query()->forCustomer('jane@example.com')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($favorite->id);
});

test('for product scope filters by product id', function () {
    $product = Product::factory()->create();
    $favorite = CustomerFavorite::factory()->create([
        'product_id' => $product->id,
    ]);
    CustomerFavorite::factory()->create();

    $results = CustomerFavorite::query()->forProduct($product->id)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($favorite->id);
});
