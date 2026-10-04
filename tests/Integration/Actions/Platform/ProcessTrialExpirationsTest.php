<?php

use App\Actions\Platform\ProcessTrialExpirations;
use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => setUpCentralTest());

test('dispatches reminders at 7, 3, and 1 day intervals', function () {
    Event::fake([TrialReminding::class]);

    foreach ([7, 3, 1] as $days) {
        $owner = User::factory()->create(['email' => "baker{$days}@test.com"]);

        createTenant([
            'id' => "expiring-{$days}d",
            'name' => 'Baker',
            'email' => "baker{$days}@test.com",
            'user_id' => $owner->id,
            'trial_ends_at' => now()->addDays($days)->startOfDay(),
            'is_active' => true,
        ]);
    }

    resolve(ProcessTrialExpirations::class)();

    foreach ([7, 3, 1] as $days) {
        Event::assertDispatched(fn (TrialReminding $event): bool => $event->daysLeft === $days && $event->user->email === "baker{$days}@test.com");
    }
});

test('skips reminder when cache marker exists', function () {
    Event::fake([TrialReminding::class]);

    $owner = User::factory()->create(['email' => 'cached@test.com']);

    createTenant([
        'id' => 'cached-bakery',
        'name' => 'Cached',
        'email' => 'cached@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);

    Cache::put('sent_trial_reminder_7d_cached-bakery', true, now()->addDays(30));

    resolve(ProcessTrialExpirations::class)();

    Event::assertNotDispatched(TrialReminding::class);
});

test('skips reminder for inactive tenant', function () {
    Event::fake([TrialReminding::class]);

    $owner = User::factory()->create(['email' => 'inactive@test.com']);

    createTenant([
        'id' => 'inactive-bakery',
        'name' => 'Inactive',
        'email' => 'inactive@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => false,
    ]);

    resolve(ProcessTrialExpirations::class)();

    Event::assertNotDispatched(TrialReminding::class);
});

test('skips reminder when tenant has no matching user', function () {
    Event::fake([TrialReminding::class]);

    createTenant([
        'id' => 'no-user-bakery',
        'name' => 'No User',
        'email' => 'nobody@test.com',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);

    resolve(ProcessTrialExpirations::class)();

    Event::assertNotDispatched(TrialReminding::class);
});

test('pauses expired storefronts and dispatches TrialExpired', function () {
    Event::fake([TrialExpired::class, TrialReminding::class]);

    $owner = User::factory()->create(['email' => 'expired@test.com']);

    createTenant([
        'id' => 'expired-bakery',
        'name' => 'Baker',
        'email' => 'expired@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->subDays(1),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    resolve(ProcessTrialExpirations::class)();

    $tenant = DB::table('tenants')->where('id', 'expired-bakery')->first();

    expect($tenant->storefront_enabled)->toBeFalsy();
    Event::assertDispatched(fn (TrialExpired $event): bool => $event->tenantId === 'expired-bakery');
});

test('does not re-pause an already-paused storefront', function () {
    Event::fake([TrialExpired::class]);

    $owner = User::factory()->create(['email' => 'already@test.com']);

    createTenant([
        'id' => 'already-paused',
        'name' => 'Baker',
        'email' => 'already@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->subDays(5),
        'is_active' => true,
        'storefront_enabled' => false,
    ]);

    resolve(ProcessTrialExpirations::class)();

    Event::assertNotDispatched(TrialExpired::class);
});

test('pauses storefront even when tenant has no user, but skips TrialExpired event', function () {
    Event::fake([TrialExpired::class]);

    createTenant([
        'id' => 'expired-no-user',
        'name' => 'Baker',
        'email' => 'nouser-expired@test.com',
        'trial_ends_at' => now()->subDays(1),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    resolve(ProcessTrialExpirations::class)();

    $tenant = DB::table('tenants')->where('id', 'expired-no-user')->first();

    expect($tenant->storefront_enabled)->toBeFalsy();
    Event::assertNotDispatched(TrialExpired::class);
});

test('returns summary counts', function () {
    Event::fake();

    $reminderOwner = User::factory()->create(['email' => 'reminder@test.com']);
    $expiredOwner = User::factory()->create(['email' => 'expired@test.com']);

    createTenant([
        'id' => 'reminder-tenant',
        'name' => 'Baker',
        'email' => 'reminder@test.com',
        'user_id' => $reminderOwner->id,
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);

    createTenant([
        'id' => 'expired-tenant',
        'name' => 'Baker',
        'email' => 'expired@test.com',
        'user_id' => $expiredOwner->id,
        'trial_ends_at' => now()->subDays(1),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    $summary = resolve(ProcessTrialExpirations::class)();

    expect($summary)->toMatchArray([
        'reminders' => 1,
        'pausings' => 1,
        'failures' => 0,
    ]);
});

test('does not pause a free-forever storefront after its trial ends', function () {
    Event::fake([TrialExpired::class]);

    $owner = User::factory()->create(['email' => 'comped@test.com']);

    createTenant([
        'id' => 'comped-bakery',
        'name' => 'Comped',
        'email' => 'comped@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->subDay(),
        'is_active' => true,
        'storefront_enabled' => true,
        'free_forever' => true,
    ]);

    resolve(ProcessTrialExpirations::class)();

    $tenant = DB::table('tenants')->where('id', 'comped-bakery')->first();

    expect($tenant->storefront_enabled)->toBeTruthy();
    Event::assertNotDispatched(TrialExpired::class);
});

test('does not send trial reminders to a free-forever tenant', function () {
    Event::fake([TrialReminding::class]);

    $owner = User::factory()->create(['email' => 'comped-reminder@test.com']);

    createTenant([
        'id' => 'comped-reminder-bakery',
        'name' => 'Comped',
        'email' => 'comped-reminder@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
        'free_forever' => true,
    ]);

    resolve(ProcessTrialExpirations::class)();

    Event::assertNotDispatched(TrialReminding::class);
});

test('does not pause a subscribed owner whose email no longer matches the bakery', function () {
    Event::fake([TrialExpired::class]);

    $owner = User::factory()->create(['email' => 'new-address@test.com']);
    $owner->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_email_changed',
        'stripe_status' => 'active',
        'stripe_price' => 'price_starter_test',
    ]);

    createTenant([
        'id' => 'renamed-owner-bakery',
        'name' => 'Renamed Owner',
        'email' => 'old-address@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->subDay(),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    resolve(ProcessTrialExpirations::class)();

    $tenant = DB::table('tenants')->where('id', 'renamed-owner-bakery')->first();

    expect($tenant->storefront_enabled)->toBeTruthy();
    Event::assertNotDispatched(TrialExpired::class);
});
