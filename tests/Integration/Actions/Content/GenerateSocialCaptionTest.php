<?php

use App\Actions\Content\GenerateSocialCaption;
use App\Models\Inventory\Product;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it generates a caption containing the product name and store hashtag', function () {
    $product = Product::factory()->create(['name' => 'Sourdough Loaf', 'price' => 8.50]);
    $tenant = Tenant::query()->make([
        'id' => 'social-caption-test',
        'name' => 'Test Bakery',
        'store_name' => 'Test Bakery',
    ]);

    tenancy()->initialize($tenant);

    $caption = (new GenerateSocialCaption)($product);

    expect($caption)->toContain('Sourdough Loaf');
});
