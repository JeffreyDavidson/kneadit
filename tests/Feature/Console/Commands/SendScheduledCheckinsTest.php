<?php

use App\Events\Platform\ScheduledCheckinDue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

beforeEach(fn () => setUpCentralTest());

test('checkins:send reports no active checkins', function () {
    pendingArtisan('checkins:send')
        ->expectsOutputToContain('No active check-ins found')
        ->assertSuccessful();
});

test('checkins:send logs the count when checkins exist', function () {
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

    pendingArtisan('checkins:send')
        ->expectsOutputToContain('1 check-in(s)')
        ->assertSuccessful();

    Event::assertDispatched(ScheduledCheckinDue::class);
});
