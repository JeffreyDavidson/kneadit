<?php

use App\Filament\Central\Pages\TenantComparison;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantComparisonQuery;

beforeEach(function () {
    setUpCentralTest();
    test()->page = new TenantComparison;
});

test('active tab defaults to compare', function () {
    expect(testFixture('page', TenantComparison::class)->activeTab)->toBe('compare');
});

test('selected tenants defaults to empty', function () {
    expect(testFixture('page', TenantComparison::class)->selectedTenants)->toBeEmpty();
});

test('get all tenants returns empty when no tenants', function () {
    expect(testFixture('page', TenantComparison::class)->getAllTenants())->toBeEmpty();
});

test('get all tenants returns tenants ordered by store name', function () {
    Tenant::factory()->create(['store_name' => 'Zebra Bakery']);
    Tenant::factory()->create(['store_name' => 'Alpha Bakery']);

    $tenants = testFixture('page', TenantComparison::class)->getAllTenants();

    expect($tenants)->toHaveCount(2)
        ->and(array_values($tenants)[0])->toBe('Alpha Bakery');
});

test('get comparison data returns empty when no tenants selected', function () {
    expect(testFixture('page', TenantComparison::class)->getComparisonData())->toBeEmpty();
});

test('get comparison data returns data for selected tenants', function () {
    $tenant = Tenant::factory()->create([
        'store_name' => 'Test Bakery',
        'storefront_enabled' => true,
        'brand_color_primary' => '#ff0000',
    ]);

    testFixture('page', TenantComparison::class)->selectedTenants = [$tenant->id];
    $data = testFixture('page', TenantComparison::class)->getComparisonData();

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

    $score = TenantComparisonQuery::calculateHealthScore($tenant, $data);

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

    $score = TenantComparisonQuery::calculateHealthScore($tenant, $data);

    expect($score)->toBe(25);
});

test('calculate health score increases with orders', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => null]);

    $noOrders = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);
    $fewOrders = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 3, 'total_products' => 0, 'setup_completed' => 0]);
    $someOrders = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 10, 'total_products' => 0, 'setup_completed' => 0]);
    $manyOrders = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 25, 'total_products' => 0, 'setup_completed' => 0]);
    $lotsOfOrders = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 50, 'total_products' => 0, 'setup_completed' => 0]);

    expect($noOrders)->toBe(0)
        ->and($fewOrders)->toBe(5)
        ->and($someOrders)->toBe(10)
        ->and($manyOrders)->toBe(20)
        ->and($lotsOfOrders)->toBe(25);
});

test('calculate health score increases with products', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => null]);

    $few = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 2, 'setup_completed' => 0]);
    $some = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 5, 'setup_completed' => 0]);
    $more = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 15, 'setup_completed' => 0]);
    $many = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 25, 'setup_completed' => 0]);

    expect($few)->toBe(5)
        ->and($some)->toBe(10)
        ->and($more)->toBe(15)
        ->and($many)->toBe(20);
});

test('calculate health score capped at 100', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()]);

    $score = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 100, 'total_products' => 50, 'setup_completed' => 7]);

    expect($score)->toBeLessThanOrEqual(100);
});

test('get leaderboard data returns all tenants', function () {
    Tenant::factory()->count(3)->create();

    $data = testFixture('page', TenantComparison::class)->getLeaderboardData();

    expect($data)->toHaveCount(3);
});

test('get leaderboard summary stats', function () {
    expect(testFixture('page', TenantComparison::class)->getLeaderboardSummaryStats())
        ->toHaveKeys(['total_orders', 'total_bakeries', 'active_bakeries', 'avg_orders_active']);
});

test('get leaderboard summary stats with no tenants', function () {
    $stats = testFixture('page', TenantComparison::class)->getLeaderboardSummaryStats();

    expect($stats['total_orders'])->toBe(0)
        ->and($stats['total_bakeries'])->toBe(0)
        ->and($stats['active_bakeries'])->toBe(0)
        ->and($stats['avg_orders_active'])->toBe(0);
});

test('calculate health score with login 3 days ago', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()->subDays(2)]);

    $score = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe(20);
});

test('calculate health score with login 5 days ago', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()->subDays(5)]);

    $score = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe(20);
});

test('calculate health score with login 20 days ago', function () {
    $tenant = Tenant::factory()->create(['last_login_at' => now()->subDays(20)]);

    $score = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe(5);
});
