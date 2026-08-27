<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantOnboardingMetrics;

/** @param array<string, mixed> $overrides */
function tenantWithOnboardingState(array $overrides = []): Tenant
{
    return (new Tenant)->forceFill(array_merge([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
        'onboarding_products_count' => 0,
        'onboarding_categories_count' => 0,
        'onboarding_orders_count' => 0,
    ], $overrides));
}

test('completed counts a configured store name', function () {
    $tenant = tenantWithOnboardingState(['store_name' => 'My Bakery']);

    $count = resolve(TenantOnboardingMetrics::class)->completed($tenant);

    expect($count)->toBeGreaterThanOrEqual(1);
});

test('completed counts individual onboarding checks', function (array $overrides, int $expected) {
    $tenant = tenantWithOnboardingState($overrides);

    $count = resolve(TenantOnboardingMetrics::class)->completed($tenant);

    expect($count)->toBe($expected);
})->with([
    'store logo' => [['store_logo' => 'logo.png'], 1],
    'storefront enabled' => [['storefront_enabled' => true], 1],
    'custom brand color' => [['brand_color_primary' => '#ff0000'], 1],
    'default brand color' => [['brand_color_primary' => '#d4920c'], 0],
]);
