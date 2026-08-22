<?php

use App\Actions\Content\ApplyProductDescription;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('applies a description to a product', function () {
    $product = Product::factory()->create(['description' => 'Old description']);

    resolve(ApplyProductDescription::class)($product, 'New amazing description');

    expect($product->refresh()->description)->toBe('New amazing description');
});
