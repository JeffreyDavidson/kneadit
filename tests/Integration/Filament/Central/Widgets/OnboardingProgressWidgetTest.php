<?php

use App\Filament\Central\Widgets\OnboardingProgress;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->widget = new OnboardingProgress;
});

test('get onboarding stats returns expected keys', function () {
    $stats = testFixture('widget', OnboardingProgress::class)->getOnboardingStats();

    expect($stats)->toHaveKeys(['onboarded', 'total', 'percentage']);
});

test('get onboarding stats with no tenants returns zeros', function () {
    $stats = testFixture('widget', OnboardingProgress::class)->getOnboardingStats();

    expect($stats['onboarded'])->toBe(0)
        ->and($stats['total'])->toBe(0)
        ->and($stats['percentage'])->toBe(0);
});

test('get onboarding stats counts tenants', function () {
    Tenant::factory()->count(3)->create();

    $stats = testFixture('widget', OnboardingProgress::class)->getOnboardingStats();

    expect($stats['total'])->toBe(3);
});

test('count completed checks store name', function () {
    $tenant = Tenant::factory()->create(['store_name' => 'My Bakery']);

    $method = new ReflectionMethod(OnboardingProgress::class, 'countCompleted');
    $count = $method->invoke(testFixture('widget', OnboardingProgress::class), $tenant);

    expect($count)->toBeGreaterThanOrEqual(1);
});

test('count completed checks store logo', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => 'logo.png',
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $method = new ReflectionMethod(OnboardingProgress::class, 'countCompleted');
    $count = $method->invoke(testFixture('widget', OnboardingProgress::class), $tenant);

    expect($count)->toBe(1);
});

test('count completed checks storefront enabled', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => true,
        'brand_color_primary' => '#d4920c',
    ]);

    $method = new ReflectionMethod(OnboardingProgress::class, 'countCompleted');
    $count = $method->invoke(testFixture('widget', OnboardingProgress::class), $tenant);

    expect($count)->toBe(1);
});

test('count completed checks brand color customized', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#ff0000',
    ]);

    $method = new ReflectionMethod(OnboardingProgress::class, 'countCompleted');
    $count = $method->invoke(testFixture('widget', OnboardingProgress::class), $tenant);

    expect($count)->toBe(1);
});

test('count completed does not count default brand color', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $method = new ReflectionMethod(OnboardingProgress::class, 'countCompleted');
    $count = $method->invoke(testFixture('widget', OnboardingProgress::class), $tenant);

    expect($count)->toBe(0);
});

test('percentage calculation is correct', function () {
    Tenant::factory()->count(4)->create([
        'store_name' => null,
        'store_logo' => null,
        'storefront_enabled' => false,
        'brand_color_primary' => '#d4920c',
    ]);

    $stats = testFixture('widget', OnboardingProgress::class)->getOnboardingStats();

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

    $stats = testFixture('widget', OnboardingProgress::class)->getOnboardingStats();

    expect($stats['onboarded'])->toBe(1)
        ->and($stats['percentage'])->toBe(100.0);
});
