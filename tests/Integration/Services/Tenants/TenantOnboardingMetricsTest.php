<?php

use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantOnboardingMetrics;

beforeEach(fn () => setUpCentralTest());

test('sync stores tenant database counts on the central tenant record', function () {
    $tenant = Tenant::factory()->create();

    $tenant->run(function (): void {
        Product::factory()->create();
        Order::factory()->create();
    });

    resolve(TenantOnboardingMetrics::class)->sync($tenant);

    $tenant->refresh();

    expect($tenant->onboarding_products_count)->toBe(1)
        ->and($tenant->onboarding_categories_count)->toBe(1)
        ->and($tenant->onboarding_orders_count)->toBe(1)
        ->and($tenant->onboarding_metrics_synced_at)->not->toBeNull();
});

test('checks use only centrally stored onboarding data', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => 'Sunrise Bakery',
        'store_logo' => 'logo.png',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
        'onboarding_products_count' => 2,
        'onboarding_categories_count' => 1,
        'onboarding_orders_count' => 3,
    ]);

    $metrics = resolve(TenantOnboardingMetrics::class);

    expect($metrics->checks($tenant))->each->toBeTrue()
        ->and($metrics->completed($tenant))->toBe(TenantOnboardingMetrics::TOTAL_CHECKS);
});
