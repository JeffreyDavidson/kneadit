<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('active scope returns only active products', function () {
    Product::factory()->create(['is_active' => true]);
    Product::factory()->create(['is_active' => false]);

    expect(Product::query()->active()->count())->toBe(1);
});

test('featured scope returns only featured products', function () {
    Product::factory()->create(['is_active' => true, 'is_featured' => true]);
    Product::factory()->create(['is_active' => true, 'is_featured' => false]);

    expect(Product::query()->featured()->count())->toBe(1);
});
