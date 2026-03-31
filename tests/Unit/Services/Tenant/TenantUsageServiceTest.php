<?php

use App\Enums\SubscriptionTier;
use App\Services\Tenant\TenantUsageService;

test('getNextPlan returns correct upgrade path', function (string $current, ?SubscriptionTier $expected) {
    $service = resolve(TenantUsageService::class);

    expect($service->getNextPlan($current))->toBe($expected);
})->with([
    'starter upgrades to Growth' => ['starter', SubscriptionTier::Growth],
    'growth upgrades to Pro' => ['growth', SubscriptionTier::Pro],
    'pro has no upgrade' => ['pro', null],
]);
