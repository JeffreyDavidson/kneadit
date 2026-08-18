<?php

use App\Models\Operations\WebhookDelivery;
use App\Services\Platform\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['webhook_url' => 'https://8.8.8.8/test']);
    settings(['webhook_secret' => 'test-secret']);
});

test('successful dispatch records a succeeded delivery row', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    resolve(WebhookService::class)->dispatch('order.created', ['order_number' => 'ORD-001']);

    $delivery = WebhookDelivery::sole();

    expect($delivery)
        ->event->toBe('order.created')
        ->url->toBe('https://8.8.8.8/test')
        ->status_code->toBe(200)
        ->response_body->toBe('ok')
        ->succeeded->toBeTrue()
        ->error->toBeNull()
        ->responded_at->not->toBeNull()
        ->and($delivery->payload)->toMatchArray([
            'event' => 'order.created',
            'data' => ['order_number' => 'ORD-001'],
        ]);
});

test('failed http response records a failed delivery row', function () {
    Http::fake(['*' => Http::response('boom', 500)]);

    resolve(WebhookService::class)->dispatch('order.updated', ['order_number' => 'ORD-002']);

    expect(WebhookDelivery::sole())
        ->status_code->toBe(500)
        ->response_body->toBe('boom')
        ->succeeded->toBeFalse();
});

test('connection exception records an error delivery row', function () {
    Http::fake(fn () => throw new Illuminate\Http\Client\ConnectionException('Connection refused'));

    resolve(WebhookService::class)->dispatch('order.cancelled', ['order_number' => 'ORD-003']);

    expect(WebhookDelivery::sole())
        ->succeeded->toBeFalse()
        ->error->toBe('Connection refused')
        ->status_code->toBeNull();
});

test('response body is truncated to ~2KB', function () {
    Http::fake(['*' => Http::response(str_repeat('x', 5000), 200)]);

    resolve(WebhookService::class)->dispatch('order.created', []);

    expect(strlen((string) WebhookDelivery::sole()->response_body))->toBe(2000);
});

test('no row is recorded when the service is not configured', function () {
    settings(['webhook_url' => '']);

    resolve(WebhookService::class)->dispatch('order.created', []);

    expect(WebhookDelivery::count())->toBe(0);
});
