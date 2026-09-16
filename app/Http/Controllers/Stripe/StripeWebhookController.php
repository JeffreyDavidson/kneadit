<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Stripe\Concerns\EnsuresWebhookIdempotency;
use App\Services\Stripe\StripeWebhookEventHandler;
use App\Services\Stripe\StripeWebhookPayloadParser;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    use EnsuresWebhookIdempotency;

    public function __construct(
        private readonly StripeWebhookPayloadParser $payloadParser,
        private readonly StripeWebhookEventHandler $eventHandler,
    ) {
        parent::__construct();
    }

    /** @param array<string, mixed> $payload */
    protected function alreadyProcessed(array $payload): bool
    {
        $eventId = $payload['id'] ?? null;

        return $this->eventAlreadyProcessed(is_string($eventId) ? $eventId : null);
    }

    /** @param array<string, mixed> $payload */
    protected function handleCustomerSubscriptionUpdated(array $payload): ?Response
    {
        if ($this->alreadyProcessed($payload)) {
            return null;
        }

        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $this->eventHandler->handleSubscriptionUpdated($this->payloadParser->object($payload));

        return $response;
    }

    /** @param array<string, mixed> $payload */
    protected function handleInvoicePaymentFailed(array $payload): void
    {
        if ($this->alreadyProcessed($payload)) {
            return;
        }

        $this->eventHandler->handleInvoicePaymentFailed($this->payloadParser->object($payload));
    }

    /** @param array<string, mixed> $payload */
    protected function handleCustomerSubscriptionDeleted(array $payload): ?Response
    {
        if ($this->alreadyProcessed($payload)) {
            return null;
        }

        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $this->eventHandler->handleSubscriptionDeleted($this->payloadParser->object($payload));

        return $response;
    }
}
