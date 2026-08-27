<?php

use App\Filament\Central\Widgets\OnboardingProgress;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->widget = new OnboardingProgress;
});

test('empty onboarding stats return expected keys and zeros', function () {
    $stats = test()->widget->getOnboardingStats();

    expect($stats)->toHaveKeys(['onboarded', 'total', 'percentage']);
    expect($stats['onboarded'])->toBe(0)
        ->and($stats['total'])->toBe(0)
        ->and($stats['percentage'])->toBe(0);
});

test('onboarding stats count incomplete tenants without percentage progress', function () {
    Tenant::factory()->count(3)->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $stats = test()->widget->getOnboardingStats();

    expect($stats['total'])->toBe(3);
    expect($stats['percentage'])->toEqual(0);
});

test('fully onboarded count uses central metrics', function () {
    Tenant::factory()->create([
        'store_name' => 'Complete Bakery',
        'store_logo' => 'logo.png',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
        'onboarding_products_count' => 1,
        'onboarding_categories_count' => 1,
        'onboarding_orders_count' => 1,
    ]);

    $stats = test()->widget->getOnboardingStats();

    expect($stats['onboarded'])->toBe(1)
        ->and($stats['percentage'])->toBe(100.0);
});
