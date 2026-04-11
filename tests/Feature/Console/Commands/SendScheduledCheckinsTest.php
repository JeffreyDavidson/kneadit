<?php

use App\Events\Platform\ScheduledCheckinDue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

beforeEach(fn () => setUpCentralTest());

test('checkins:send command runs successfully with no active checkins', function () {
    $this->artisan('checkins:send')
        ->expectsOutputToContain('No active check-ins found')
        ->assertSuccessful();
});

test('checkins:send dispatches event for matching tenant', function () {
    Event::fake([ScheduledCheckinDue::class]);

    $daysAgo = 7;

    DB::table('scheduled_checkins')->insert([
        'name' => 'Week 1 Checkin',
        'days_after_signup' => $daysAgo,
        'subject' => 'How is it going?',
        'body' => 'Welcome email body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createTenant([
        'id' => 'matching-bakery',
        'name' => 'Matching Baker',
        'email' => 'matching@test.com',
        'created_at' => now()->subDays($daysAgo)->startOfDay(),
        'updated_at' => now()->subDays($daysAgo)->startOfDay(),
    ]);

    $this->artisan('checkins:send')
        ->expectsOutputToContain('1 check-in(s)')
        ->assertSuccessful();

    Event::assertDispatched(ScheduledCheckinDue::class, function ($event) {
        return $event->tenantEmail === 'matching@test.com'
            && $event->subject === 'How is it going?';
    });
});

test('checkins:send creates a log entry when sending', function () {
    Event::fake([ScheduledCheckinDue::class]);

    $daysAgo = 3;

    DB::table('scheduled_checkins')->insert([
        'id' => 99,
        'name' => 'Day 3 Checkin',
        'days_after_signup' => $daysAgo,
        'subject' => 'Day 3 subject',
        'body' => 'Day 3 body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createTenant([
        'id' => 'log-bakery',
        'name' => 'Log Baker',
        'email' => 'log@test.com',
        'created_at' => now()->subDays($daysAgo)->startOfDay(),
        'updated_at' => now()->subDays($daysAgo)->startOfDay(),
    ]);

    $this->artisan('checkins:send')
        ->assertSuccessful();

    $log = DB::table('checkin_logs')
        ->where('checkin_id', 99)
        ->where('tenant_id', 'log-bakery')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->sent_at)->not->toBeNull();
});

test('checkins:send skips tenant already sent this checkin', function () {
    Event::fake([ScheduledCheckinDue::class]);

    $daysAgo = 5;

    DB::table('scheduled_checkins')->insert([
        'id' => 50,
        'name' => 'Day 5 Checkin',
        'days_after_signup' => $daysAgo,
        'subject' => 'Already sent subject',
        'body' => 'Already sent body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createTenant([
        'id' => 'already-sent-bakery',
        'name' => 'Already Sent Baker',
        'email' => 'already@test.com',
        'created_at' => now()->subDays($daysAgo)->startOfDay(),
        'updated_at' => now()->subDays($daysAgo)->startOfDay(),
    ]);

    DB::table('checkin_logs')->insert([
        'checkin_id' => 50,
        'tenant_id' => 'already-sent-bakery',
        'sent_at' => now()->subDay(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('checkins:send')
        ->expectsOutputToContain('0 check-in(s)')
        ->assertSuccessful();

    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('checkins:send skips tenant with no email', function () {
    Event::fake([ScheduledCheckinDue::class]);

    $daysAgo = 2;

    DB::table('scheduled_checkins')->insert([
        'name' => 'Day 2 Checkin',
        'days_after_signup' => $daysAgo,
        'subject' => 'No email subject',
        'body' => 'No email body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tenants')->insert([
        'id' => 'no-email-bakery',
        'name' => 'No Email Baker',
        'email' => '',
        'plan' => 'starter',
        'is_active' => true,
        'storefront_enabled' => true,
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
        'created_at' => now()->subDays($daysAgo)->startOfDay(),
        'updated_at' => now()->subDays($daysAgo)->startOfDay(),
    ]);

    $this->artisan('checkins:send')
        ->assertSuccessful();

    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('checkins:send skips inactive checkins', function () {
    Event::fake([ScheduledCheckinDue::class]);

    DB::table('scheduled_checkins')->insert([
        'name' => 'Inactive Checkin',
        'days_after_signup' => 1,
        'subject' => 'Inactive subject',
        'body' => 'Inactive body',
        'is_active' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createTenant([
        'id' => 'inactive-checkin-bakery',
        'name' => 'Baker',
        'email' => 'baker@test.com',
        'created_at' => now()->subDay()->startOfDay(),
        'updated_at' => now()->subDay()->startOfDay(),
    ]);

    $this->artisan('checkins:send')
        ->expectsOutputToContain('No active check-ins')
        ->assertSuccessful();

    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('checkins:send does not match non-matching tenants', function () {
    Event::fake([ScheduledCheckinDue::class]);

    DB::table('scheduled_checkins')->insert([
        'name' => 'Day 10 Checkin',
        'days_after_signup' => 10,
        'subject' => 'Day 10 subject',
        'body' => 'Day 10 body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createTenant([
        'id' => 'wrong-date-bakery',
        'name' => 'Wrong Date Baker',
        'email' => 'wrong@test.com',
        'created_at' => now()->subDays(5)->startOfDay(),
        'updated_at' => now()->subDays(5)->startOfDay(),
    ]);

    $this->artisan('checkins:send')
        ->expectsOutputToContain('0 check-in(s)')
        ->assertSuccessful();

    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('checkins:send handles dispatch exception gracefully', function () {
    Event::shouldReceive('dispatch')
        ->andThrow(new Exception('Event dispatch failed'));

    Log::shouldReceive('error')
        ->atLeast()
        ->once();

    $daysAgo = 1;

    DB::table('scheduled_checkins')->insert([
        'name' => 'Day 1 Checkin',
        'days_after_signup' => $daysAgo,
        'subject' => 'Error subject',
        'body' => 'Error body',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    createTenant([
        'id' => 'error-checkin-bakery',
        'name' => 'Error Baker',
        'email' => 'error@test.com',
        'created_at' => now()->subDays($daysAgo)->startOfDay(),
        'updated_at' => now()->subDays($daysAgo)->startOfDay(),
    ]);

    $this->artisan('checkins:send')
        ->assertSuccessful();
});
