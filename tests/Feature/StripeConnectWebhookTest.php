<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    config(['database.connections.central' => config('database.connections.sqlite')]);

    DB::purge('central');
    $pdo = DB::connection('sqlite')->getPdo();
    DB::connection('central')->setPdo($pdo)->setReadPdo($pdo);

    createCentralTables();
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
