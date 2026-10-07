<?php

use App\Events\Platform\TrialExpired;
use App\Events\Platform\TrialReminding;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => setUpCentralTest());

test('trial:check command runs successfully', function () {
    Event::fake();

    $this->artisan('trial:check')->assertSuccessful();
});

test('trial:check reports reminders and pausings in output', function () {
    Event::fake();

    $owner = User::factory()->create(['email' => 'baker7@test.com']);
    createTenant([
        'id' => 'expiring-7d',
        'name' => 'Baker',
        'email' => 'baker7@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->addDays(7)->startOfDay(),
    ]);

    $this->artisan('trial:check')
        ->expectsOutputToContain('1 reminders sent')
        ->assertSuccessful();

    Event::assertDispatched(TrialReminding::class);
});

test('trial:check pauses expired storefronts via the action', function () {
    Event::fake();

    $owner = User::factory()->create(['email' => 'expired@test.com']);
    createTenant([
        'id' => 'expired-bakery',
        'name' => 'Baker',
        'email' => 'expired@test.com',
        'user_id' => $owner->id,
        'trial_ends_at' => now()->subDays(1),
        'storefront_enabled' => true,
    ]);

    $this->artisan('trial:check')
        ->expectsOutputToContain('1 storefronts paused')
        ->assertSuccessful();

    Event::assertDispatched(TrialExpired::class);
});
