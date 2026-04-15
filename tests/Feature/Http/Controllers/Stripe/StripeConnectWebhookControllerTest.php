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
    config(['kneadit.stripe_connect.webhook_secret' => 'whsec_test_secret']);

    $response = postJson('/stripe/connect-webhook', [
        'type' => 'account.updated',
        'data' => ['object' => ['id' => 'acct_123']],
    ], [
        'Stripe-Signature' => 'invalid_signature',
    ]);

    $response->assertStatus(400);
});

test('webhook returns 500 when secret not configured', function () {
    config(['kneadit.stripe_connect.webhook_secret' => null]);

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
    config(['kneadit.stripe_connect.webhook_secret' => null]);

    $payload = json_encode([
        'type' => 'some.unknown.event',
        'data' => ['object' => ['id' => 'obj_123']],
    ]);

    $response = test()->call('POST', '/stripe/connect-webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertStatus(500);
});

test('webhook controller routes account.updated to HandleConnectAccountUpdated', function () {
    $source = file_get_contents(app_path('Http/Controllers/Stripe/StripeConnectWebhookController.php'));

    expect($source)
        ->toContain("'account.updated' => resolve(HandleConnectAccountUpdated::class)")
        ->toContain("'checkout.session.completed' => resolve(HandleConnectCheckoutCompleted::class)")
        ->toContain('default => null');
});

test('webhook controller implements idempotency via cache', function () {
    $controllerSource = file_get_contents(app_path('Http/Controllers/Stripe/StripeConnectWebhookController.php'));
    $traitSource = file_get_contents(app_path('Http/Controllers/Stripe/Concerns/EnsuresWebhookIdempotency.php'));

    expect($controllerSource)
        ->toContain('EnsuresWebhookIdempotency')
        ->toContain('Already processed')
        ->and($traitSource)
        ->toContain('Cache::add("stripe_event:{$eventId}"');
});

test('webhook controller verifies stripe signature', function () {
    $source = file_get_contents(app_path('Http/Controllers/Stripe/StripeConnectWebhookController.php'));

    expect($source)
        ->toContain('Webhook::constructEvent')
        ->toContain('Stripe-Signature')
        ->toContain('Invalid signature');
});
