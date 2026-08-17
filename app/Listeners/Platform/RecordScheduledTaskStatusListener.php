<?php

namespace App\Listeners\Platform;

use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

class RecordScheduledTaskStatusListener
{
    public function __construct(
        private ScheduledTaskMonitor $monitor,
    ) {}

    public function handle(ScheduledTaskStarting|ScheduledTaskFinished|ScheduledTaskFailed $event): void
    {
        $task = $event->task->getSummaryForDisplay();

        match (true) {
            $event instanceof ScheduledTaskStarting => $this->monitor->started($task),
            $event instanceof ScheduledTaskFinished => $this->monitor->succeeded($task, $event->runtime, $event->task->exitCode),
            $event instanceof ScheduledTaskFailed => $this->monitor->failed($task, $event->exception),
        };
    }
}
