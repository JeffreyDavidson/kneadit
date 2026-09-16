<?php

namespace App\Http\Controllers\Stripe;

use App\Services\Stripe\StripeWebhookEventHandler;
use App\Services\Stripe\StripeWebhookIdempotency;
use App\Services\Stripe\StripeWebhookPayloadParser;
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

    /** @param array<string, mixed> $payload */
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
