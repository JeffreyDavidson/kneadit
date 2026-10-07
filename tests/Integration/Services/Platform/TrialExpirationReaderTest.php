<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Platform\TrialExpirationReader;

beforeEach(fn () => setUpCentralTest());

test('tenantsRemindable yields tenants whose trial ends on the target date', function () {
    createTenant([
        'id' => 'reminder-target',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
    ]);
    createTenant([
        'id' => 'wrong-date',
        'trial_ends_at' => now()->addDays(5)->startOfDay(),
    ]);

    $tenants = iterator_to_array(resolve(TrialExpirationReader::class)->tenantsRemindable(7));

    expect($tenants)->toHaveCount(1)
        ->and($tenants[0]->id)->toBe('reminder-target');
});

test('the reader ignores the retired is_active column, because Pause is the only switch', function () {
    createTenant([
        'id' => 'old-deactivated-reminder',
        'trial_ends_at' => now()->addDays(3)->startOfDay(),
        'is_active' => false,
    ]);
    createTenant([
        'id' => 'old-deactivated-expired',
        'trial_ends_at' => now()->subDay(),
        'is_active' => false,
    ]);

    $reader = resolve(TrialExpirationReader::class);

    expect(collect(iterator_to_array($reader->tenantsRemindable(3), false))->pluck('id')->all())->toBe(['old-deactivated-reminder'])
        ->and(collect(iterator_to_array($reader->tenantsExpired(), false))->pluck('id')->all())->toBe(['old-deactivated-expired']);
});

test('tenantsExpired yields tenants whose trial passed and that are not paused yet, whatever their storefront setting', function () {
    createTenant([
        'id' => 'expired-active',
        'trial_ends_at' => now()->subDay(),
        'storefront_enabled' => true,
    ]);
    createTenant([
        'id' => 'expired-external-site',
        'trial_ends_at' => now()->subDay(),
        'storefront_enabled' => false,
    ]);
    createTenant([
        'id' => 'expired-paused',
        'trial_ends_at' => now()->subDay(),
        'paused_at' => now()->subHour(),
    ]);
    createTenant([
        'id' => 'still-trialing',
        'trial_ends_at' => now()->addDay(),
        'storefront_enabled' => true,
    ]);

    $tenants = collect(iterator_to_array(resolve(TrialExpirationReader::class)->tenantsExpired(), false))
        ->pluck('id')
        ->sort()
        ->values()
        ->all();

    expect($tenants)->toBe(['expired-active', 'expired-external-site']);
});

test('userFor returns the owner linked by user_id even when their email has changed', function () {
    $owner = User::factory()->create(['email' => 'new-address@example.com']);
    createTenant(['id' => 'baker-bakery', 'email' => 'old-address@example.com', 'user_id' => $owner->id]);
    $tenant = Tenant::query()->find('baker-bakery');

    expect(resolve(TrialExpirationReader::class)->userFor($tenant)?->id)->toBe($owner->id);
});

test('userFor returns null when the tenant has no linked owner, even if a user shares its email', function () {
    User::factory()->create(['email' => 'baker@example.com']);
    createTenant(['id' => 'unowned', 'email' => 'baker@example.com', 'user_id' => null]);
    $tenant = Tenant::query()->find('unowned');

    expect(resolve(TrialExpirationReader::class)->userFor($tenant))->toBeNull();
});
