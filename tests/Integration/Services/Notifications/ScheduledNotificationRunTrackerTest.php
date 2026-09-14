<?php

use App\Services\Notifications\ScheduledNotificationRunTracker;

beforeEach(fn () => setUpTenantTest());

it('claims a notification key only once', function () {
    $tracker = resolve(ScheduledNotificationRunTracker::class);

    expect($tracker->claim('weekly-digest:2026-09-14:1'))->toBeTrue()
        ->and($tracker->claim('weekly-digest:2026-09-14:1'))->toBeFalse();

    test()->assertDatabaseCount('scheduled_notification_runs', 1);
});
