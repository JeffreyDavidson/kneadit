<?php

use App\Models\Operations\CheckinLog;
use App\Models\Operations\ScheduledCheckin;
use Carbon\Carbon;

beforeEach(fn () => setUpCentralTest());

test('can create a checkin log', function () {
    $log = CheckinLog::factory()->create();

    expect(CheckinLog::query()->find($log->id))->not->toBeNull();
});

test('sent at is cast to datetime', function () {
    $log = CheckinLog::factory()->create([
        'sent_at' => '2026-02-01 09:00:00',
    ]);

    $log->refresh();

    expect($log->sent_at)->toBeInstanceOf(Carbon::class);
});

test('checkin relationship returns the parent scheduled checkin', function () {
    $checkin = ScheduledCheckin::factory()->create();
    $log = CheckinLog::factory()->create([
        'checkin_id' => $checkin->id,
    ]);

    expect($log->checkin->id)->toBe($checkin->id);
});
