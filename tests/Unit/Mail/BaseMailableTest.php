<?php

use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;

it('implements ShouldQueue with default retry config', function () {
    $mailable = new class extends BaseMailable {};

    expect($mailable)->toBeInstanceOf(ShouldQueue::class);
    expect($mailable->tries)->toBe(3);
    expect($mailable->backoff)->toBe([10, 60, 300]);
});
