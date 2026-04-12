<?php

use App\Listeners\QueuedListener;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

test('QueuedListener has default retry configuration', function () {
    $ref = new ReflectionClass(QueuedListener::class);

    $tries = $ref->getAttributes(Tries::class)[0]->newInstance();
    $timeout = $ref->getAttributes(Timeout::class)[0]->newInstance();
    $backoff = $ref->getAttributes(Backoff::class)[0]->newInstance();

    expect($tries->tries)->toBe(3)
        ->and($timeout->timeout)->toBe(60)
        ->and($backoff->backoff)->toBe([10, 60, 300]);
});
