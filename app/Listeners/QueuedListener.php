<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

abstract class QueuedListener implements ShouldQueue
{
    public int $timeout = 60;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];
}
