<?php

use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    setUpCentralTest();
});

test('trial check command exists', function () {
    Event::fake();

    $this->artisan('trial:check')
        ->assertSuccessful();
});

test('7 day reminder dispatches event', function () {
    Event::fake([TrialReminding::class]);

    User::factory()->create([
        'email' => 'baker7@test.com',
    ]);

    createTenant([
        'id' => 'expiring-7d',
        'name' => 'Baker',
        'email' => 'baker7@test.com',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);

    $this->artisan('trial:check')
        ->expectsOutputToContain('7d reminder');

    Event::assertDispatched(TrialReminding::class, function ($event) {
        return $event->daysLeft === 7
            && $event->user->email === 'baker7@test.com';
    });
});

test('3 day reminder dispatches event', function () {
    Event::fake([TrialReminding::class]);

    User::factory()->create([
        'email' => 'baker3@test.com',
    ]);

    createTenant([
        'id' => 'expiring-3d',
        'name' => 'Baker',
        'email' => 'baker3@test.com',
        'trial_ends_at' => now()->addDays(3)->startOfDay(),
        'is_active' => true,
    ]);

    $this->artisan('trial:check')
        ->expectsOutputToContain('3d reminder');

    Event::assertDispatched(TrialReminding::class, function ($event) {
        return $event->daysLeft === 3;
    });
});

test('1 day reminder dispatches event', function () {
    Event::fake([TrialReminding::class]);

    User::factory()->create([
        'email' => 'baker1@test.com',
    ]);

    createTenant([
        'id' => 'expiring-1d',
        'name' => 'Baker',
        'email' => 'baker1@test.com',
        'trial_ends_at' => now()->addDays(1)->startOfDay(),
        'is_active' => true,
    ]);

    $this->artisan('trial:check')
        ->expectsOutputToContain('1d reminder');

    Event::assertDispatched(TrialReminding::class, function ($event) {
        return $event->daysLeft === 1;
    });
});

test('expired trial pauses storefront', function () {
    Event::fake([TrialExpired::class, TrialReminding::class]);

    User::factory()->create([
        'email' => 'expired@test.com',
    ]);

    createTenant([
        'id' => 'expired-bakery',
        'name' => 'Baker',
        'email' => 'expired@test.com',
        'trial_ends_at' => now()->subDays(1),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    $this->artisan('trial:check');

    $tenant = DB::table('tenants')
        ->where('id', 'expired-bakery')
        ->first();

    expect((bool) $tenant->storefront_enabled)->toBeFalse();
});

test('expired trial dispatches TrialExpired event', function () {
    Event::fake([TrialExpired::class, TrialReminding::class]);

    User::factory()->create([
        'email' => 'expired-event@test.com',
    ]);

    createTenant([
        'id' => 'expired-event-bakery',
        'name' => 'Baker',
        'email' => 'expired-event@test.com',
        'trial_ends_at' => now()->subDays(1),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    $this->artisan('trial:check');

    Event::assertDispatched(TrialExpired::class, function ($event) {
        return $event->tenantId === 'expired-event-bakery';
    });
});

test('expired storefront already paused not repaused', function () {
    Event::fake([TrialExpired::class, TrialReminding::class]);

    User::factory()->create([
        'email' => 'already@test.com',
    ]);

    createTenant([
        'id' => 'already-paused',
        'name' => 'Baker',
        'email' => 'already@test.com',
        'trial_ends_at' => now()->subDays(5),
        'is_active' => true,
        'storefront_enabled' => false,
    ]);

    $this->artisan('trial:check')
        ->assertSuccessful();

    Event::assertNotDispatched(TrialExpired::class);
});

test('no action for inactive tenant', function () {
    Event::fake([TrialReminding::class]);

    User::factory()->create([
        'email' => 'inactive@test.com',
    ]);

    createTenant([
        'id' => 'inactive-bakery',
        'name' => 'Inactive',
        'email' => 'inactive@test.com',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => false,
    ]);

    $this->artisan('trial:check')
        ->assertSuccessful();

    Event::assertNotDispatched(TrialReminding::class);
});

test('no action when trial far away', function () {
    Event::fake([TrialReminding::class]);

    User::factory()->create([
        'email' => 'new@test.com',
    ]);

    createTenant([
        'id' => 'new-bakery',
        'name' => 'New',
        'email' => 'new@test.com',
        'trial_ends_at' => now()->addDays(25),
        'is_active' => true,
    ]);

    $this->artisan('trial:check')
        ->assertSuccessful();

    Event::assertNotDispatched(TrialReminding::class);
});

test('reminder skips tenant with no matching user', function () {
    Event::fake([TrialReminding::class]);

    createTenant([
        'id' => 'no-user-bakery',
        'name' => 'No User',
        'email' => 'nobody@test.com',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);

    $this->artisan('trial:check')
        ->assertSuccessful();

    Event::assertNotDispatched(TrialReminding::class);
});

test('reminder skips when already sent via cache', function () {
    Event::fake([TrialReminding::class]);

    User::factory()->create([
        'email' => 'cached@test.com',
    ]);

    createTenant([
        'id' => 'cached-bakery',
        'name' => 'Cached',
        'email' => 'cached@test.com',
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
        'is_active' => true,
    ]);

    Cache::put('sent_trial_reminder_7d_cached-bakery', true, now()->addDays(30));

    $this->artisan('trial:check')
        ->assertSuccessful();

    Event::assertNotDispatched(TrialReminding::class);
});

test('expired handler skips tenant with no user', function () {
    Event::fake([TrialExpired::class, TrialReminding::class]);

    createTenant([
        'id' => 'expired-no-user',
        'name' => 'Baker',
        'email' => 'nouser-expired@test.com',
        'trial_ends_at' => now()->subDays(1),
        'is_active' => true,
        'storefront_enabled' => true,
    ]);

    $this->artisan('trial:check');

    // Storefront should still be paused even without a user
    $tenant = DB::table('tenants')
        ->where('id', 'expired-no-user')
        ->first();

    expect((bool) $tenant->storefront_enabled)->toBeFalse();

    // But no TrialExpired event dispatched (no user to send to)
    Event::assertNotDispatched(TrialExpired::class);
});

test('command source sends at three intervals', function () {
    $source = file_get_contents(app_path('Console/Commands/Platform/CheckTrialExpirationsCommand.php'));

    expect($source)->toContain('trial_reminder_7d')->toContain('trial_reminder_3d')->toContain('trial_reminder_1d');
});
