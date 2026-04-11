<?php

use App\Enums\Platform\SubscriptionTier;
use App\Services\Tenants\TenancyManager;
use App\Services\Tenants\TenantUsageService;

test('getNextPlan returns correct upgrade path', function (string $current, ?SubscriptionTier $expected) {
    $service = new TenantUsageService(Mockery::mock(TenancyManager::class));

    expect($service->getNextPlan($current))->toBe($expected);
})->with([
    'starter upgrades to Growth' => ['starter', SubscriptionTier::Growth],
    'growth upgrades to Pro' => ['growth', SubscriptionTier::Pro],
    'pro has no upgrade' => ['pro', null],
    'unknown plan returns null' => ['enterprise', null],
]);
