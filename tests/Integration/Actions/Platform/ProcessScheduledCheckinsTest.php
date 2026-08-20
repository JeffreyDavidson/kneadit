<?php

use App\Actions\Platform\ProcessScheduledCheckins;
use App\Events\Platform\ScheduledCheckinDue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

beforeEach(fn () => setUpCentralTest());

test('returns no_active_checkins flag when none are active', function () {
    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['no_active_checkins'])->toBeTrue()
        ->and($summary['sent'])->toBe(0);
});

test('dispatches event and logs when a tenant matches a checkin', function () {
    Event::fake([ScheduledCheckinDue::class]);

    $daysAgo = 7;

    DB::table('scheduled_checkins')->insert([
        'id' => 10,
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

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['sent'])->toBe(1);

    Event::assertDispatched(fn (ScheduledCheckinDue $event): bool => $event->tenantEmail === 'matching@test.com'
        && $event->subject === 'How is it going?');

    $log = DB::table('checkin_logs')
        ->where('checkin_id', 10)
        ->where('tenant_id', 'matching-bakery')
        ->firstOrFail();

    expect($log)->not->toBeNull();
});

test('skips tenant that was already sent the same checkin', function () {
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

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['sent'])->toBe(0);
    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('skips tenant with empty email and increments counter', function () {
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

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['sent'])->toBe(0)
        ->and($summary['skipped_no_email'])->toBe(1);
    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('ignores inactive checkins', function () {
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

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['no_active_checkins'])->toBeTrue();
    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('does not match tenants signed up on a non-target date', function () {
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

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['sent'])->toBe(0);
    Event::assertNotDispatched(ScheduledCheckinDue::class);
});

test('catches dispatch exceptions and counts them as failures', function () {
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

    $summary = resolve(ProcessScheduledCheckins::class)();

    expect($summary['sent'])->toBe(0)
        ->and($summary['failures'])->toBe(1);
});
