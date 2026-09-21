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

test('completes claimed events and rejects duplicate claims', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    $idempotency->claim('evt_complete');
    $idempotency->complete('evt_complete');

    expect($idempotency->claim('evt_complete'))->toBeFalse();
});

test('events without identifiers do not block processing', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    expect($idempotency->claim(null))->toBeTrue()
        ->and($idempotency->process(null, fn () => 'first'))->toBe('first')
        ->and($idempotency->process(null, fn () => 'second'))->toBe('second');

    $idempotency->complete(null);
    $idempotency->release(null);

    expect(Cache::get('stripe_event:'))->toBeNull();
});

test('completes an event only after its callback succeeds', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    $result = $idempotency->process('evt_success', fn () => 'handled');

    expect($result)->toBe('handled')
        ->and($idempotency->claim('evt_success'))->toBeFalse();
});

test('releases a failed event claim so the event can be retried', function () {
    $idempotency = resolve(StripeWebhookIdempotency::class);

    expect(fn () => $idempotency->process('evt_retry_after_failure', function () {
        throw new RuntimeException('temporary processing failure');
    }))->toThrow(RuntimeException::class, 'temporary processing failure')
        ->and($idempotency->process('evt_retry_after_failure', fn () => 'retried'))->toBe('retried')
        ->and($idempotency->process('evt_retry_after_failure', fn () => 'duplicate'))->toBeNull();
});
