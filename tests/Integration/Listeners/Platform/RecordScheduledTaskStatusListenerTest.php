<?php

use App\Listeners\Platform\RecordScheduledTaskStatusListener;
use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Console\Events\ScheduledBackgroundTaskFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Date;

beforeEach(fn () => setUpCentralTest());

test('records scheduled task lifecycle', function () {
    $task = resolve(Schedule::class)->command('health:check')->name('health:check');
    $listener = resolve(RecordScheduledTaskStatusListener::class);

    $listener->handle(new ScheduledTaskStarting($task));
    expect(resolve(ScheduledTaskMonitor::class)->status('health:check')['status'])->toBe('running');

    $task->exitCode = 0;
    $listener->handle(new ScheduledTaskFinished($task, 1.23456));

    expect(resolve(ScheduledTaskMonitor::class)->status('health:check'))
        ->toMatchArray([
            'status' => 'succeeded',
            'runtime_seconds' => 1.235,
            'exit_code' => 0,
        ]);
});

test('records scheduled task failures without exposing unbounded messages', function () {
    $task = resolve(Schedule::class)->command('health:check')->name('health:check');

    resolve(RecordScheduledTaskStatusListener::class)->handle(
        new ScheduledTaskFailed($task, new RuntimeException(str_repeat('failure', 100))),
    );

    $status = resolve(ScheduledTaskMonitor::class)->status('health:check');

    expect($status['status'])->toBe('failed')
        ->and(mb_strlen($status['error']))->toBe(500);
});

describe('background tasks', function () {
    beforeEach(function () {
        Date::setTestNow('2026-10-05 09:00:00');
        test()->task = resolve(Schedule::class)->command('health:check')->runInBackground()->name('health:check');
        test()->listener = resolve(RecordScheduledTaskStatusListener::class);

        test()->listener->handle(new ScheduledTaskStarting(test()->task));
    });

    test('are running once started', function () {
        expect(resolve(ScheduledTaskMonitor::class)->status('health:check'))
            ->toMatchArray(['status' => 'running', 'started_at' => '2026-10-05T09:00:00+00:00']);
    });

    test('ignore the finished event that fires right after launch, before the task has run', function () {
        Date::setTestNow('2026-10-05 09:00:01');

        test()->listener->handle(new ScheduledTaskFinished(test()->task, 0.012));

        expect(resolve(ScheduledTaskMonitor::class)->status('health:check'))
            ->toMatchArray(['status' => 'running', 'finished_at' => null, 'exit_code' => null]);
    });

    test('are succeeded when the background run finishes with exit code 0', function () {
        test()->listener->handle(new ScheduledTaskFinished(test()->task, 0.012));
        Date::setTestNow('2026-10-05 09:00:42');
        test()->task->exitCode = 0;

        test()->listener->handle(new ScheduledBackgroundTaskFinished(test()->task));

        expect(resolve(ScheduledTaskMonitor::class)->status('health:check'))
            ->toMatchArray([
                'status' => 'succeeded',
                'finished_at' => '2026-10-05T09:00:42+00:00',
                'runtime_seconds' => 42.0,
                'exit_code' => 0,
            ]);
    });

    test('are failed with the exit code when the background run finishes with a non-zero code', function () {
        test()->listener->handle(new ScheduledTaskFinished(test()->task, 0.012));
        Date::setTestNow('2026-10-05 09:00:03');
        test()->task->exitCode = 1;

        test()->listener->handle(new ScheduledBackgroundTaskFinished(test()->task));

        expect(resolve(ScheduledTaskMonitor::class)->status('health:check'))
            ->toMatchArray(['status' => 'failed', 'runtime_seconds' => 3.0, 'exit_code' => 1]);
    });
});

test('a foreground task failing its exit code is recorded as failed by the finished event', function () {
    $task = resolve(Schedule::class)->command('health:check')->name('health:check');
    $task->exitCode = 2;

    resolve(RecordScheduledTaskStatusListener::class)->handle(new ScheduledTaskFinished($task, 0.5));

    expect(resolve(ScheduledTaskMonitor::class)->status('health:check'))
        ->toMatchArray(['status' => 'failed', 'exit_code' => 2]);
});
