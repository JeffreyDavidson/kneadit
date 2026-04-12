<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

#[Timeout(60)]
#[Tries(3)]
#[Backoff([10, 60, 300])]
abstract class QueuedListener implements ShouldQueue {}
