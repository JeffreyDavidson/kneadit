<?php

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

test('webhook handles account updated without secret', function () {
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

    $response->assertOk();
    $response->assertSee('OK');
});

test('webhook returns ok for unknown event type', function () {
    config(['saas.stripe_connect.webhook_secret' => null]);

    $payload = json_encode([
        'type' => 'some.unknown.event',
        'data' => ['object' => ['id' => 'obj_123']],
    ]);

    $response = test()->call('POST', '/stripe/connect-webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertOk();
});
