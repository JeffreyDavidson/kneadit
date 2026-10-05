<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantComparisonQuery;

beforeEach(fn () => setUpCentralTest());

test('the storefront setup step counts for a bakery that uses its own website', function (bool $storefrontEnabled, ?string $externalWebsite, int $expectedSetupCompleted) {
    $tenant = Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => $storefrontEnabled,
        'external_website' => $externalWebsite,
        'brand_color_primary' => '#d4920c',
    ]);

    $results = resolve(TenantComparisonQuery::class)->comparison([$tenant->id]);

    expect($results[0]->setupCompleted)->toBe($expectedSetupCompleted);
})->with([
    'KneadIt storefront' => [true, null, 1],
    'own website' => [false, 'https://sunrise.example', 1],
    'neither' => [false, null, 0],
]);
