<?php

use App\Filament\Central\Pages\OnboardingTracker;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new OnboardingTracker;
});

test('get tenant onboarding data returns empty when no tenants', function () {
    expect(test()->page->getTenantOnboardingData())->toBeEmpty();
});

test('get tenant onboarding data returns data for each tenant', function () {
    Tenant::factory()->create(['store_name' => 'Bakery A']);
    Tenant::factory()->create(['store_name' => 'Bakery B']);

    $data = test()->page->getTenantOnboardingData();

    expect($data)->toHaveCount(2);
});

test('get tenant onboarding data includes expected keys', function () {
    Tenant::factory()->create(['store_name' => 'Test Bakery']);

    $data = test()->page->getTenantOnboardingData();
    $tenant = $data->first();

    expect($tenant)->toHaveKeys([
        'id', 'name', 'subdomain', 'owner', 'email',
        'plan', 'created_at', 'days_since_signup',
        'checks', 'completed', 'total',
    ]);
});

test('get tenant onboarding data checks store name', function () {
    Tenant::factory()->create(['store_name' => 'My Bakery']);

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['checks']['store_name'])->toBeTrue();
});

test('get tenant onboarding data checks empty store name', function () {
    Tenant::factory()->create(['store_name' => null]);

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['checks']['store_name'])->toBeFalse();
});

test('get tenant onboarding data checks storefront enabled', function () {
    Tenant::factory()->create(['storefront_enabled' => true]);

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['checks']['storefront_enabled'])->toBeTrue();
});

test('get tenant onboarding data checks brand customized', function () {
    Tenant::factory()->create(['brand_color_primary' => '#ff0000']);

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['checks']['brand_customized'])->toBeTrue();
});

test('get tenant onboarding data detects default brand color', function () {
    Tenant::factory()->create(['brand_color_primary' => '#d4920c']);

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['checks']['brand_customized'])->toBeFalse();
});

test('get onboarding data reads tenant content checks from central metrics', function () {
    Tenant::factory()->create([
        'onboarding_products_count' => 2,
        'onboarding_categories_count' => 1,
        'onboarding_orders_count' => 0,
    ]);

    $checks = test()->page->getTenantOnboardingData()->first()['checks'];

    expect($checks['has_products'])->toBeTrue()
        ->and($checks['has_categories'])->toBeTrue()
        ->and($checks['has_orders'])->toBeFalse();
});

test('total checks is always 7', function () {
    Tenant::factory()->create();

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['total'])->toBe(7);
});

test('get summary stats returns expected keys', function () {
    $stats = test()->page->getSummaryStats();

    expect($stats)->toHaveKeys(['total', 'fully_onboarded', 'needs_attention']);
});

test('get summary stats with no tenants returns zeros', function () {
    $stats = test()->page->getSummaryStats();

    expect($stats['total'])->toBe(0)
        ->and($stats['fully_onboarded'])->toBe(0)
        ->and($stats['needs_attention'])->toBe(0);
});

test('get summary stats counts tenants needing attention', function () {
    // Tenant with minimal setup (needs attention)
    Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $stats = test()->page->getSummaryStats();

    expect($stats['total'])->toBe(1)
        ->and($stats['needs_attention'])->toBe(1);
});

test('onboarding data sorted by completion then date', function () {
    Tenant::factory()->create([
        'store_name' => 'Complete Bakery',
        'store_logo' => 'logo.png',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
    ]);
    Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $data = test()->page->getTenantOnboardingData();

    expect($data->first()['completed'])->toBeLessThanOrEqual($data->last()['completed']);
});
