<?php

use function Pest\Laravel\postJson;

beforeEach(function () {
    config(['tenancy.central_domains' => ['localhost']]);
});

test('webhook returns 400 for invalid signature when secret configured', function () {
    config(['saas.stripe_connect.webhook_secret' => 'whsec_test_secret']);

    $response = postJson('/stripe/connect-webhook', [
        'type' => 'account.updated',
        'data' => ['object' => ['id' => 'acct_123']],
    ], [
        'Stripe-Signature' => 'invalid_signature',
    ]);

    $response->assertStatus(400);
});

test('webhook returns 500 when secret not configured', function () {
    config(['saas.stripe_connect.webhook_secret' => null]);

    $payload = json_encode([
        'type' => 'account.updated',
        'data' => [
            'object' => [
                'id' => 'acct_123',
                'charges_enabled' => true,
                'metadata' => ['tenant_id' => 'nonexistent-tenant'],
            ],
        ],
    ]);

    $response = test()->call('POST', '/stripe/connect-webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertStatus(500);
    $response->assertSee('Webhook secret not configured');
});

test('webhook returns 500 for unknown event type without secret', function () {
    config(['saas.stripe_connect.webhook_secret' => null]);

    $payload = json_encode([
        'type' => 'some.unknown.event',
        'data' => ['object' => ['id' => 'obj_123']],
    ]);

    $response = test()->call('POST', '/stripe/connect-webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertStatus(500);
});
