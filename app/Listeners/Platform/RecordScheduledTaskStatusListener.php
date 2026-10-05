<?php

namespace App\Listeners\Platform;

use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Console\Events\ScheduledBackgroundTaskFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

class RecordScheduledTaskStatusListener
{
    public function __construct(
        private readonly ScheduledTaskMonitor $monitor,
    ) {}

    public function handle(ScheduledTaskStarting|ScheduledTaskFinished|ScheduledBackgroundTaskFinished|ScheduledTaskFailed $event): void
    {
        // A background task's ScheduledTaskFinished fires right after it is launched, with no exit code yet.
        // Its real outcome arrives later in ScheduledBackgroundTaskFinished.
        if ($event instanceof ScheduledTaskFinished && $event->task->runInBackground) {
            return;
        }

        $task = $event->task->getSummaryForDisplay();

        match (true) {
            $event instanceof ScheduledTaskStarting => $this->monitor->started($task),
            $event instanceof ScheduledTaskFinished => $this->monitor->succeeded($task, $event->runtime, $event->task->exitCode),
            $event instanceof ScheduledBackgroundTaskFinished => $this->monitor->succeeded($task, $this->monitor->secondsSinceStart($task), $event->task->exitCode),
            $event instanceof ScheduledTaskFailed => $this->monitor->failed($task, $event->exception),
        };
    }
}
