<?php

use App\Mail\BaseMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;

it('implements ShouldQueue with default retry config', function () {
    $ref = new ReflectionClass(BaseMailable::class);

    $tries = $ref->getAttributes(Tries::class)[0]->newInstance();
    $backoff = $ref->getAttributes(Backoff::class)[0]->newInstance();

    expect(new class extends BaseMailable {})->toBeInstanceOf(ShouldQueue::class)
        ->and($tries->tries)->toBe(3)
        ->and($backoff->backoff)->toBe([10, 60, 300]);
});
