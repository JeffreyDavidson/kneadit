<?php

use App\Listeners\Platform\RecordScheduledTaskStatusListener;
use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Schedule;

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
