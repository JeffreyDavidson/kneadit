<?php

use App\Services\Stripe\StripeWebhookIdempotency;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['cache.default' => 'array']);
    Cache::flush();
});

test('claims an event once and allows retry after release', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    expect($idempotency->claim('evt_retry'))->toBeTrue()
        ->and($idempotency->claim('evt_retry'))->toBeFalse();

    $idempotency->release('evt_retry');

    expect($idempotency->claim('evt_retry'))->toBeTrue();
});

test('completes claimed events and reports duplicate events as processed', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    $idempotency->claim('evt_complete');
    $idempotency->complete('evt_complete');

    expect($idempotency->claim('evt_complete'))->toBeFalse()
        ->and($idempotency->alreadyProcessed('evt_complete'))->toBeTrue();
});

test('events without identifiers do not block processing', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    expect($idempotency->claim(null))->toBeTrue()
        ->and($idempotency->alreadyProcessed(null))->toBeFalse();

    $idempotency->complete(null);
    $idempotency->release(null);

    expect(Cache::get('stripe_event:'))->toBeNull();
});
