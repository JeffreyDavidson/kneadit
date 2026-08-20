<?php

use App\Actions\Operations\SendTestWebhook;
use App\Models\Operations\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['webhook_url' => 'https://8.8.8.8/test']);
    settings(['webhook_secret' => 'test-secret']);
});

test('sends a synthetic order.created payload flagged with test:true', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    resolve(SendTestWebhook::class)();

    Http::assertSent(function (Request $request): bool {
        $body = json_decode($request->body(), true);

        return $request->hasHeader('X-KneadIt-Event', 'order.created')
            && data_get($body, 'event') === 'order.created'
            && data_get($body, 'data.test') === true
            && data_get($body, 'data.order_number') === 'TEST-0001';
    });

    $delivery = WebhookDelivery::sole();
    expect($delivery->event)->toBe('order.created')
        ->and($delivery->succeeded)->toBeTrue();
});
