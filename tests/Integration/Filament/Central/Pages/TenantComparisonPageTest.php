<?php

use App\Filament\Central\Pages\TenantComparison;
use App\Models\Platform\Tenant;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new TenantComparison;
});

test('active tab defaults to compare', function () {
    expect(test()->page->activeTab)->toBe('compare');
});

test('selected tenants defaults to empty', function () {
    expect(test()->page->selectedTenants)->toBeEmpty();
});

test('get all tenants returns empty when no tenants', function () {
    expect(test()->page->getAllTenants())->toBeEmpty();
});

test('get all tenants returns tenants ordered by store name', function () {
    Tenant::factory()->create(['store_name' => 'Zebra Bakery']);
    Tenant::factory()->create(['store_name' => 'Alpha Bakery']);

    $tenants = test()->page->getAllTenants();

    expect($tenants)->toHaveCount(2)
        ->and(array_values($tenants)[0])->toBe('Alpha Bakery');
});

test('get comparison data returns empty when no tenants selected', function () {
    expect(test()->page->getComparisonData())->toBeEmpty();
});

test('get comparison data returns data for selected tenants', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => 'Test Bakery',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
    ]);

    test()->page->selectedTenants = [$tenant->id];
    $data = test()->page->getComparisonData();

    expect($data)->toHaveCount(1)
        ->and($data[0])->toHaveKeys(['id', 'name', 'plan', 'total_orders', 'days_since_signup', 'setup_completed', 'health_score']);
});

test('calculate health score returns zero for inactive tenant', function () {
    $tenant = Tenant::factory()->create([
        'last_login_at' => null,
    ]);

    $data = [
        'total_orders' => 0,
        'total_products' => 0,
        'setup_completed' => 0,
    ];

    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');
    $score = $method->invoke(test()->page, $tenant, $data);

    expect($score)->toBe(0);
});

test('calculate health score increases with recent login', function () {
    $tenant = Tenant::factory()->create([
        'last_login_at' => now(),
    ]);

    $data = [
        'total_orders' => 0,
        'total_products' => 0,
        'setup_completed' => 0,
    ];

    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');
    $score = $method->invoke(test()->page, $tenant, $data);

    expect($score)->toBe(30);
});

test('calculate health score increases with orders', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => null]);

    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');

    $noOrders = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);
    $fewOrders = $method->invoke(test()->page, $tenant, ['total_orders' => 3, 'total_products' => 0, 'setup_completed' => 0]);
    $someOrders = $method->invoke(test()->page, $tenant, ['total_orders' => 10, 'total_products' => 0, 'setup_completed' => 0]);
    $manyOrders = $method->invoke(test()->page, $tenant, ['total_orders' => 25, 'total_products' => 0, 'setup_completed' => 0]);
    $lotsOfOrders = $method->invoke(test()->page, $tenant, ['total_orders' => 50, 'total_products' => 0, 'setup_completed' => 0]);

    expect($noOrders)->toBe(0)
        ->and($fewOrders)->toBe(5)
        ->and($someOrders)->toBe(10)
        ->and($manyOrders)->toBe(20)
        ->and($lotsOfOrders)->toBe(30);
});

test('calculate health score increases with products', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => null]);

    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');

    $few = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 2, 'setup_completed' => 0]);
    $some = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 5, 'setup_completed' => 0]);
    $more = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 15, 'setup_completed' => 0]);
    $many = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 25, 'setup_completed' => 0]);

    expect($few)->toBe(5)
        ->and($some)->toBe(10)
        ->and($more)->toBe(15)
        ->and($many)->toBe(20);
});

test('calculate health score capped at 100', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()]);

    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');
    $score = $method->invoke(test()->page, $tenant, ['total_orders' => 100, 'total_products' => 50, 'setup_completed' => 7]);

    expect($score)->toBeLessThanOrEqual(100);
});

test('get leaderboard data returns all tenants', function () {
    Tenant::factory()->count(3)->create();

    $data = test()->page->getLeaderboardData();

    expect($data)->toHaveCount(3);
});

test('get leaderboard summary stats', function () {
    expect(test()->page->getLeaderboardSummaryStats())
        ->toHaveKeys(['total_orders', 'avg_orders', 'total_bakeries']);
});

test('get leaderboard summary stats with no tenants', function () {
    $stats = test()->page->getLeaderboardSummaryStats();

    expect($stats['total_orders'])->toBe(0)
        ->and($stats['avg_orders'])->toBe(0)
        ->and($stats['total_bakeries'])->toBe(0);
});

test('calculate health score with login 3 days ago', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()->subDays(2)]);
    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');
    $score = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe(25);
});

test('calculate health score with login 5 days ago', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()->subDays(5)]);
    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');
    $score = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe(15);
});

test('calculate health score with login 20 days ago', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()->subDays(20)]);
    $method = new ReflectionMethod(TenantComparison::class, 'calculateHealthScore');
    $score = $method->invoke(test()->page, $tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe(5);
});
