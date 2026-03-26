<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpCentralTest();
});

test('trial check command exists', function () {
    Mail::fake();

    $this->artisan('trial:check')
        ->assertSuccessful();
});

test('7 day reminder sends email', function () {
    Mail::fake();

    User::query()->create([
        'name' => 'Baker',
        'email' => 'baker7@test.com',
        'password' => bcrypt('password'),
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
});

test('expired trial pauses storefront', function () {
    Mail::fake();

    User::query()->create([
        'name' => 'Baker',
        'email' => 'expired@test.com',
        'password' => bcrypt('password'),
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

test('expired storefront already paused not repaused', function () {
    Mail::fake();

    User::query()->create([
        'name' => 'Baker',
        'email' => 'already@test.com',
        'password' => bcrypt('password'),
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
});

test('no action for inactive tenant', function () {
    Mail::fake();

    User::query()->create([
        'name' => 'Inactive',
        'email' => 'inactive@test.com',
        'password' => bcrypt('password'),
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

    Mail::assertNothingSent();
});

test('no action when trial far away', function () {
    Mail::fake();

    User::query()->create([
        'name' => 'New',
        'email' => 'new@test.com',
        'password' => bcrypt('password'),
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

    Mail::assertNothingSent();
});

test('command source sends at three intervals', function () {
    $source = file_get_contents(app_path('Console/Commands/CheckTrialExpirations.php'));

    expect($source)->toContain('trial_reminder_7d');
    expect($source)->toContain('trial_reminder_3d');
    expect($source)->toContain('trial_reminder_1d');
});

test('expired handler sends expiration email', function () {
    $source = file_get_contents(app_path('Console/Commands/CheckTrialExpirations.php'));

    expect(file_get_contents(resource_path('views/emails/trial-expired-text.blade.php')))->toContain('trial has expired');
    expect(file_get_contents(resource_path('views/emails/trial-expired-text.blade.php')))->toContain('storefront has been paused');
});
