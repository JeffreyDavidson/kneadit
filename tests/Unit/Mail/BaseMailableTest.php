<?php

use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\SendQueuedMailable;

it('queues mail with bounded retries and delivery time', function () {
    $mailable = new class extends BaseMailable {};
    $queuedMailable = new SendQueuedMailable($mailable);

    expect($mailable)->toBeInstanceOf(ShouldQueue::class)
        ->and($queuedMailable->tries)->toBe(3)
        ->and($queuedMailable->backoff())->toBe([10, 60, 300])
        ->and($queuedMailable->timeout)->toBe(60)
        ->and(config('mail.mailers.smtp.timeout'))->toBe(10);
});
