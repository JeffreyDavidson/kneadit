<?php

use App\Services\Tenant\TenantUsageService;

test('getNextPlan returns correct upgrade path', function (string $current, ?string $expected) {
    $service = resolve(TenantUsageService::class);

    expect($service->getNextPlan($current))->toBe($expected);
})->with([
    'starter upgrades to Growth' => ['starter', 'Growth'],
    'growth upgrades to Pro' => ['growth', 'Pro'],
    'pro has no upgrade' => ['pro', null],
]);
