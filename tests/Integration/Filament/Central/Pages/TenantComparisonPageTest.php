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

test('get leaderboard data returns all tenants', function () {
    Tenant::factory()->count(3)->create();

    $data = test()->page->getLeaderboardData();

    expect($data)->toHaveCount(3);
});

test('get leaderboard summary stats', function () {
    expect(test()->page->getLeaderboardSummaryStats())
        ->toHaveKeys(['total_orders', 'total_bakeries', 'active_bakeries', 'avg_orders_active']);
});

test('get leaderboard summary stats with no tenants', function () {
    $stats = test()->page->getLeaderboardSummaryStats();

    expect($stats['total_orders'])->toBe(0)
        ->and($stats['total_bakeries'])->toBe(0)
        ->and($stats['active_bakeries'])->toBe(0)
        ->and($stats['avg_orders_active'])->toBe(0);
});
