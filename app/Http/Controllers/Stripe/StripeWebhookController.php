<?php

namespace App\Http\Controllers\Stripe;

use App\Services\Stripe\StripeWebhookEventHandler;
use App\Services\Stripe\StripeWebhookIdempotency;
use App\Services\Stripe\StripeWebhookPayloadParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    public function __construct(
        private readonly StripeWebhookPayloadParser $payloadParser,
        private readonly StripeWebhookEventHandler $eventHandler,
        private readonly StripeWebhookIdempotency $idempotency,
    ) {
        parent::__construct();
    }

    /**
     * Cashier only verifies signatures when a secret is configured, so refuse
     * to process anything without one outside local development and tests.
     */
    #[\Override]
    public function handleWebhook(Request $request): ?Response
    {
        $secret = Config::get('cashier.webhook.secret');

        if ((! is_string($secret) || $secret === '') && ! app()->environment(['local', 'testing'])) {
            Log::error('STRIPE_WEBHOOK_SECRET not configured');

            return response('Webhook secret not configured', 500);
        }

        return parent::handleWebhook($request);
    }

    /** @param array<string, mixed> $payload */
    #[\Override]
    protected function handleCustomerSubscriptionUpdated(array $payload): ?Response
    {
        $eventId = is_string($payload['id'] ?? null) ? $payload['id'] : null;

        return $this->idempotency->process($eventId, function () use ($payload) {
            $response = parent::handleCustomerSubscriptionUpdated($payload);

            $this->eventHandler->handleSubscriptionUpdated($this->payloadParser->object($payload));

            return $response;
        });
    }

    /** @param array<string, mixed> $payload */
    protected function handleInvoicePaymentFailed(array $payload): void
    {
        $eventId = is_string($payload['id'] ?? null) ? $payload['id'] : null;

        $this->idempotency->process($eventId, function () use ($payload): void {
            $this->eventHandler->handleInvoicePaymentFailed($this->payloadParser->object($payload));
        });
    }

    /** @param array<string, mixed> $payload */
    #[\Override]
    protected function handleCustomerSubscriptionDeleted(array $payload): ?Response
    {
        $eventId = is_string($payload['id'] ?? null) ? $payload['id'] : null;

        return $this->idempotency->process($eventId, function () use ($payload) {
            $response = parent::handleCustomerSubscriptionDeleted($payload);

            $this->eventHandler->handleSubscriptionDeleted($this->payloadParser->object($payload));

            return $response;
        });
    }
}
