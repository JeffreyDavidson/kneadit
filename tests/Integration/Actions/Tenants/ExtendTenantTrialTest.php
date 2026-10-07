<?php

use App\Actions\Tenants\ExtendTenantTrial;
use App\Models\Platform\Tenant;

beforeEach(fn () => setUpCentralTest());

test('extends tenant trial by specified days', function () {
    createTenant(['trial_ends_at' => now()->addDays(5)]);
    $tenant = Tenant::query()->find('test-bakery');

    $newEnd = resolve(ExtendTenantTrial::class)($tenant, 14);

    expect($newEnd->toDateString())->toBe(now()->addDays(19)->toDateString());
});

test('extending a trial brings back a bakery that was paused when it ended', function () {
    createTenant(['trial_ends_at' => now()->subDays(3), 'paused_at' => now()->subDays(2)]);
    $tenant = Tenant::query()->find('test-bakery');

    $newEnd = resolve(ExtendTenantTrial::class)($tenant, 30);

    $tenant->refresh();

    expect($newEnd->isFuture())->toBeTrue()
        ->and($tenant->paused_at)->toBeNull()
        ->and($tenant->trial_ends_at->toDateString())->toBe(now()->addDays(30)->toDateString());
});

test('extending a trial never shortens it', function () {
    createTenant(['trial_ends_at' => now()->addDays(90)]);
    $tenant = Tenant::query()->find('test-bakery');

    resolve(ExtendTenantTrial::class)($tenant, 30);

    expect($tenant->refresh()->trial_ends_at->toDateString())->toBe(now()->addDays(120)->toDateString());
});
