<?php

use App\Models\Inventory\Product;
use App\View\Components\Home\FeaturedProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads active products up to configured count', function () {
    Product::factory()->count(5)->create();
    Product::factory()->inactive()->create();

    $component = new FeaturedProducts(['count' => 3]);

    expect($component->featuredProducts)->toHaveCount(3)
        ->and($component->title)->toBe('Our Favorites');
});
