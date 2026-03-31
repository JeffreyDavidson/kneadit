<?php

use App\Actions\Content\GenerateSocialCaption;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it generates a caption containing the product name and store hashtag', function () {
    $product = Product::factory()->create(['name' => 'Sourdough Loaf', 'price' => 8.50]);

    $caption = (new GenerateSocialCaption)($product);

    expect($caption)->toContain('Sourdough Loaf');
});
