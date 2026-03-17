<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConnectWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The webhook route is central (no tenancy), ensure localhost is treated as central
        config(['tenancy.central_domains' => ['localhost']]);
    }

    /** @test */
    public function webhook_returns_400_for_invalid_signature_when_secret_configured(): void
    {
        config(['saas.stripe_connect.webhook_secret' => 'whsec_test_secret']);

        $response = $this->postJson('/stripe/connect-webhook', [
            'type' => 'account.updated',
            'data' => ['object' => ['id' => 'acct_123']],
        ], [
            'Stripe-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function webhook_handles_account_updated_without_secret(): void
    {
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

        $response = $this->call('POST', '/stripe/connect-webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertOk();
        $response->assertSee('OK');
    }

    /** @test */
    public function webhook_returns_ok_for_unknown_event_type(): void
    {
        config(['saas.stripe_connect.webhook_secret' => null]);

        $payload = json_encode([
            'type' => 'some.unknown.event',
            'data' => ['object' => ['id' => 'obj_123']],
        ]);

        $response = $this->call('POST', '/stripe/connect-webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertOk();
    }
}
