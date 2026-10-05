<?php

use App\Models\Operations\ActivityLog;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\artisan;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    runCommandsAsOneTenant();
    Date::setTestNow('2026-10-05 12:00');
});

test('deletes activity older than 365 days and keeps the rest', function () {
    $stale = ActivityLog::factory()->create(['created_at' => '2025-10-04 11:59']);
    $boundary = ActivityLog::factory()->create(['created_at' => '2025-10-05 12:00']);
    $fresh = ActivityLog::factory()->create(['created_at' => '2026-09-01 12:00']);

    artisan('activity-log:prune')->assertSuccessful();

    expect(ActivityLog::query()->whereKey($stale->id)->exists())->toBeFalse()
        ->and(ActivityLog::query()->whereKey($boundary->id)->exists())->toBeTrue()
        ->and(ActivityLog::query()->whereKey($fresh->id)->exists())->toBeTrue();
});

test('respects the days option', function () {
    $old = ActivityLog::factory()->create(['created_at' => '2026-09-01 12:00']);

    artisan('activity-log:prune', ['--days' => 30])->assertSuccessful();

    expect(ActivityLog::query()->whereKey($old->id)->exists())->toBeFalse();
});

test('rejects a retention period below one day', function () {
    ActivityLog::factory()->create(['created_at' => '2020-01-01 00:00']);

    artisan('activity-log:prune', ['--days' => 0])->assertFailed();

    expect(ActivityLog::query()->count())->toBe(1);
});

test('is scheduled daily', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains($event->command, 'activity-log:prune'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('45 4 * * *');
});
