<?php

use App\Filament\Central\Pages\OnboardingTracker;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new OnboardingTracker;
});

test('empty tracker returns no data and zeroed summary', function () {
    expect(test()->page->getTenantOnboardingData())->toBeEmpty();
    $stats = test()->page->getSummaryStats();

    expect($stats)->toHaveKeys(['total', 'fully_onboarded', 'needs_attention']);
    expect($stats['total'])->toBe(0)
        ->and($stats['fully_onboarded'])->toBe(0)
        ->and($stats['needs_attention'])->toBe(0);
});

test('tracker builds and sorts configured and incomplete tenant records', function () {
    $configuredTenant = Tenant::factory()->create([
        'store_name' => 'Configured Bakery',
        'store_logo' => 'logo.png',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
        'onboarding_products_count' => 2,
        'onboarding_categories_count' => 1,
        'onboarding_orders_count' => 0,
    ]);
    $incompleteTenant = Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $data = test()->page->getTenantOnboardingData();
    $records = $data->keyBy('id');
    $configured = $records[$configuredTenant->id];
    $incomplete = $records[$incompleteTenant->id];

    expect($data)->toHaveCount(2);
    expect($configured)->toHaveKeys([
        'id', 'name', 'subdomain', 'owner', 'email',
        'plan', 'created_at', 'days_since_signup',
        'checks', 'completed', 'total',
    ]);
    expect($configured['checks']['store_name'])->toBeTrue();
    expect($incomplete['checks']['store_name'])->toBeFalse();
    expect($configured['checks']['storefront_enabled'])->toBeTrue();
    expect($configured['checks']['brand_customized'])->toBeTrue();
    expect($incomplete['checks']['brand_customized'])->toBeFalse();
    expect($configured['checks']['has_products'])->toBeTrue()
        ->and($configured['checks']['has_categories'])->toBeTrue()
        ->and($configured['checks']['has_orders'])->toBeFalse();
    expect($configured['total'])->toBe(7);
    expect($data->first()['completed'])->toBeLessThanOrEqual($data->last()['completed']);
});

test('get summary stats counts tenants needing attention', function () {
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
