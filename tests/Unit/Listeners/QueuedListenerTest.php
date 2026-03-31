<?php

use App\Listeners\QueuedListener;

test('QueuedListener has default retry configuration', function () {
    $listener = new class extends QueuedListener {
        public function handle(): void {}
    };

    expect($listener->tries)->toBe(3)
        ->and($listener->timeout)->toBe(60)
        ->and($listener->backoff)->toBe([10, 60, 300]);
});
