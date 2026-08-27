<?php

use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantComparisonQuery;

test('calculate health score returns zero for inactive tenant', function () {
    $tenant = new Tenant(['last_login_at' => null]);
    $data = [
        'total_orders' => 0,
        'total_products' => 0,
        'setup_completed' => 0,
    ];

    $score = TenantComparisonQuery::calculateHealthScore($tenant, $data);

    expect($score)->toBe(0);
});

test('calculate health score increases with recent login', function () {
    $tenant = new Tenant(['last_login_at' => now()]);
    $data = [
        'total_orders' => 0,
        'total_products' => 0,
        'setup_completed' => 0,
    ];

    $score = TenantComparisonQuery::calculateHealthScore($tenant, $data);

    expect($score)->toBe(25);
});

test('calculate health score increases with orders', function () {
    $tenant = new Tenant(['last_login_at' => null]);

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
    $tenant = new Tenant(['last_login_at' => null]);

    $few = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 2, 'setup_completed' => 0]);
    $some = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 5, 'setup_completed' => 0]);
    $more = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 15, 'setup_completed' => 0]);
    $many = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 25, 'setup_completed' => 0]);

    expect($few)->toBe(5)
        ->and($some)->toBe(10)
        ->and($more)->toBe(15)
        ->and($many)->toBe(20);
});

test('calculate health score is capped at 100', function () {
    $tenant = new Tenant(['last_login_at' => now()]);

    $score = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 100, 'total_products' => 50, 'setup_completed' => 7]);

    expect($score)->toBeLessThanOrEqual(100);
});

test('calculate health score awards recent login points', function (int $daysAgo, int $expected) {
    $tenant = new Tenant(['last_login_at' => now()->subDays($daysAgo)]);

    $score = TenantComparisonQuery::calculateHealthScore($tenant, ['total_orders' => 0, 'total_products' => 0, 'setup_completed' => 0]);

    expect($score)->toBe($expected);
})->with([
    '2 days ago' => [2, 20],
    '5 days ago' => [5, 20],
    '20 days ago' => [20, 5],
]);
