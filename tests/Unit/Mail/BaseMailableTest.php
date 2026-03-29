<?php

use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;

it('implements ShouldQueue with default retry config', function () {
    $mailable = new class extends BaseMailable {};

    expect($mailable)->toBeInstanceOf(ShouldQueue::class)->and($mailable->tries)->toBe(3)->and($mailable->backoff)->toBe([10, 60, 300]);
});
