<?php

use App\Actions\Content\GenerateSocialCaption;
use App\Models\Inventory\Product;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it generates a caption containing the product name and store hashtag', function () {
    $product = Product::factory()->create(['name' => 'Sourdough Loaf', 'price' => 8.50]);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize(new Tenant([
        'id' => 'caption-bakery',
        'store_name' => 'Caption Bakery',
    ]));

    $caption = (new GenerateSocialCaption)($product);

    expect($caption)->toContain('Sourdough Loaf');
});
