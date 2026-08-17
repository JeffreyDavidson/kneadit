<?php

use App\Enums\Platform\SubscriptionTier;
use App\Services\Tenants\TenancyManager;
use App\Services\Tenants\TenantUsageService;

beforeEach(fn () => setUpCentralTest());

test('getNextPlan returns correct upgrade path', function (string $current, ?SubscriptionTier $expected) {
    $service = new TenantUsageService(Tests\Support\TypedMock::make(TenancyManager::class));

    expect($service->getNextPlan($current))->toBe($expected);
})->with([
    'starter upgrades to Growth' => ['starter', SubscriptionTier::Growth],
    'growth upgrades to Pro' => ['growth', SubscriptionTier::Pro],
    'pro has no upgrade' => ['pro', null],
    'unknown plan returns null' => ['enterprise', null],
]);

test('getTenantUsageData returns tenants approaching limits', function () {
    config([
        'kneadit.plans.starter.limits' => ['products' => 10, 'orders_per_month' => 20],
        'kneadit.plans.starter.name' => 'Starter',
    ]);

    createTenant([
        'id' => 'high-usage',
        'name' => 'High Usage Baker',
        'email' => 'high@test.com',
        'plan' => SubscriptionTier::Starter,
        'store_name' => 'High Usage Bakery',
    ]);

    $tenancyManager = Tests\Support\TypedMock::make(TenancyManager::class);
    $tenancyManager->allows(['withinTenant' => [9, 18]]); // 90% products, 90% orders

    $service = new TenantUsageService($tenancyManager);
    $result = $service->getTenantUsageData();
    $usage = $result->first();

    if ($usage === null) {
        throw new RuntimeException('Expected usage data for the tenant.');
    }

    expect($result)->toHaveCount(1)
        ->and($usage['product_percent'])->toBe(90.0)
        ->and($usage['order_percent'])->toBe(90.0)
        ->and($usage['approaching_limit'])->toBeTrue();
});

test('getTenantUsageData skips pro tenants', function () {
    createTenant([
        'id' => 'pro-bakery',
        'name' => 'Pro Baker',
        'email' => 'pro@test.com',
        'plan' => SubscriptionTier::Pro,
    ]);

    $tenancyManager = Tests\Support\TypedMock::make(TenancyManager::class);
    // Should never be called for pro tenants
    $tenancyManager->shouldNotReceive('withinTenant');

    $service = new TenantUsageService($tenancyManager);
    $result = $service->getTenantUsageData();

    expect($result)->toBeEmpty();
});

test('getTenantUsageData skips tenants below 80 percent usage', function () {
    config([
        'kneadit.plans.starter.limits' => ['products' => 100, 'orders_per_month' => 100],
    ]);

    createTenant([
        'id' => 'low-usage',
        'name' => 'Low Usage Baker',
        'email' => 'low@test.com',
        'plan' => SubscriptionTier::Starter,
    ]);

    $tenancyManager = Tests\Support\TypedMock::make(TenancyManager::class);
    $tenancyManager->allows(['withinTenant' => [5, 5]]); // 5% usage

    $service = new TenantUsageService($tenancyManager);
    $result = $service->getTenantUsageData();

    expect($result)->toBeEmpty();
});

test('getTenantUsageData handles exception gracefully', function () {
    createTenant([
        'id' => 'error-bakery',
        'name' => 'Error Baker',
        'email' => 'error@test.com',
        'plan' => SubscriptionTier::Starter,
    ]);

    $tenancyManager = Tests\Support\TypedMock::make(TenancyManager::class);
    $expectation = $tenancyManager->shouldReceive('withinTenant');

    if (! $expectation instanceof Mockery\Expectation) {
        throw new RuntimeException('Expected a concrete Mockery expectation.');
    }

    $expectation->andThrow(new RuntimeException('DB error'));

    $service = new TenantUsageService($tenancyManager);
    $result = $service->getTenantUsageData();

    expect($result)->toBeEmpty();
});

test('getTenantUsageData marks at_limit when at 100 percent', function () {
    config([
        'kneadit.plans.starter.limits' => ['products' => 10, 'orders_per_month' => 20],
    ]);

    createTenant([
        'id' => 'maxed-bakery',
        'name' => 'Maxed Baker',
        'email' => 'maxed@test.com',
        'plan' => SubscriptionTier::Starter,
    ]);

    $tenancyManager = Tests\Support\TypedMock::make(TenancyManager::class);
    $tenancyManager->allows(['withinTenant' => [10, 20]]); // 100% usage

    $service = new TenantUsageService($tenancyManager);
    $result = $service->getTenantUsageData();
    $usage = $result->first();

    if ($usage === null) {
        throw new RuntimeException('Expected usage data for the tenant.');
    }

    expect($result)->toHaveCount(1)
        ->and($usage['at_limit'])->toBeTrue()
        ->and($usage['approaching_limit'])->toBeFalse();
});
