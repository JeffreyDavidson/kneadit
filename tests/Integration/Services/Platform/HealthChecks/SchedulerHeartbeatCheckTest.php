<?php

use App\Services\Platform\HealthChecks\SchedulerHeartbeatCheck;
use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpCentralTest();
    Date::setTestNow('2026-10-05 12:00');
});

test('it fails when no scheduled task has ever started', function () {
    $result = resolve(SchedulerHeartbeatCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Scheduler silent: no scheduled task has started yet');
});

test('it passes when a task started within the last 70 minutes', function (string $startedAt) {
    Date::setTestNow($startedAt);
    resolve(ScheduledTaskMonitor::class)->started('health:check');
    Date::setTestNow('2026-10-05 12:00');

    $result = resolve(SchedulerHeartbeatCheck::class)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe("Scheduler running (last task started {$startedAt})");
})->with([
    'a minute ago' => '2026-10-05 11:59',
    'just inside the limit' => '2026-10-05 10:50',
]);

test('it fails when the latest start is older than 70 minutes', function () {
    Date::setTestNow('2026-10-05 10:49');
    resolve(ScheduledTaskMonitor::class)->started('health:check');
    Date::setTestNow('2026-10-05 12:00');

    $result = resolve(SchedulerHeartbeatCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Scheduler silent: no scheduled task has started since 2026-10-05 10:49');
});

test('it uses the most recent start across all tasks, whatever their outcome', function () {
    $monitor = resolve(ScheduledTaskMonitor::class);
    Date::setTestNow('2026-10-05 08:00');
    $monitor->started('digest:weekly');
    Date::setTestNow('2026-10-05 11:30');
    $monitor->started('health:check');
    $monitor->failed('health:check', 'boom');
    Date::setTestNow('2026-10-05 12:00');

    $result = resolve(SchedulerHeartbeatCheck::class)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Scheduler running (last task started 2026-10-05 11:30)');
});
