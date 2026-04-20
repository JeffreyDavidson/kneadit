<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Platform\TrialExpirationReader;

beforeEach(fn () => setUpCentralTest());

test('tenantsRemindable yields tenants whose trial ends on the target date', function () {
    createTenant([
        'id' => 'reminder-target',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);
    createTenant([
        'id' => 'wrong-date',
        'trial_ends_at' => now()->addDays(5)->startOfDay(),
        'is_active' => true,
    ]);

    $tenants = iterator_to_array(resolve(TrialExpirationReader::class)->tenantsRemindable(7));

    expect($tenants)->toHaveCount(1)
        ->and($tenants[0]->id)->toBe('reminder-target');
});

test('tenantsRemindable skips inactive tenants', function () {
    createTenant([
        'id' => 'inactive',
        'trial_ends_at' => now()->addDays(3)->startOfDay(),
        'is_active' => false,
    ]);

    expect(iterator_to_array(resolve(TrialExpirationReader::class)->tenantsRemindable(3)))->toBeEmpty();
});

test('tenantsExpired yields tenants whose trial passed and storefront still on', function () {
    createTenant([
        'id' => 'expired-active',
        'trial_ends_at' => now()->subDay(),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);
    createTenant([
        'id' => 'expired-storefront-off',
        'trial_ends_at' => now()->subDay(),
        'is_active' => true,
        'storefront_enabled' => false,
    ]);
    createTenant([
        'id' => 'still-trialing',
        'trial_ends_at' => now()->addDay(),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    $tenants = iterator_to_array(resolve(TrialExpirationReader::class)->tenantsExpired());

    expect($tenants)->toHaveCount(1)
        ->and($tenants[0]->id)->toBe('expired-active');
});

test('userFor returns the user matching tenant email', function () {
    $user = User::factory()->create(['email' => 'baker@example.com']);
    createTenant(['id' => 'baker-bakery', 'email' => 'baker@example.com']);
    $tenant = Tenant::query()->find('baker-bakery');

    expect(resolve(TrialExpirationReader::class)->userFor($tenant)->id)->toBe($user->id);
});

test('userFor returns null when no user matches the tenant email', function () {
    createTenant(['id' => 'orphan', 'email' => 'noone@example.com']);
    $tenant = Tenant::query()->find('orphan');

    expect(resolve(TrialExpirationReader::class)->userFor($tenant))->toBeNull();
});
