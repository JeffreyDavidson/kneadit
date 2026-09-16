<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\SyncSubscriptionPlan;
use App\Events\Platform\PaymentFailed;
use App\Http\Controllers\Stripe\Concerns\EnsuresWebhookIdempotency;
use App\Queries\Platform\StripeCustomerLookupQuery;
use App\Services\Stripe\StripeWebhookPayloadParser;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    use EnsuresWebhookIdempotency;

    public function __construct(private readonly StripeWebhookPayloadParser $payloadParser)
    {
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

        $subscription = $this->payloadParser->object($payload);
        $stripeCustomerId = $this->payloadParser->stringValue($subscription['customer'] ?? null);
        $stripePriceId = $this->payloadParser->stringValue(data_get($subscription, 'items.data.0.price.id'));

        if ($stripeCustomerId && $stripePriceId) {
            $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

            if ($lookup['user']) {
                resolve(SyncSubscriptionPlan::class)(
                    tenantEmail: $lookup['user']->email,
                    stripePriceId: $stripePriceId,
                    priceMap: $this->payloadParser->priceMap(),
                );
            }
        }

        return $response;
    }

    /** @param array<string, mixed> $payload */
    protected function handleInvoicePaymentFailed(array $payload): void
    {
        if ($this->alreadyProcessed($payload)) {
            return;
        }

        $invoice = $this->payloadParser->object($payload);
        $stripeCustomerId = $this->payloadParser->stringValue($invoice['customer'] ?? null);

        if (! $stripeCustomerId) {
            return;
        }

        $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

        if (! $lookup['user']) {
            return;
        }

        $amountDue = $invoice['amount_due'] ?? 0;
        $amountDueInDollars = is_int($amountDue) ? $amountDue / 100 : 0.0;

        Log::warning('Payment failed', [
            'tenant' => $lookup['tenant']?->id,
            'email' => $lookup['user']->email,
            'amount' => $amountDueInDollars,
        ]);

        event(new PaymentFailed($lookup['user'], $lookup['tenant'], $amountDueInDollars));
    }

    /** @param array<string, mixed> $payload */
    protected function handleCustomerSubscriptionDeleted(array $payload): ?Response
    {
        if ($this->alreadyProcessed($payload)) {
            return null;
        }

        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $subscription = $this->payloadParser->object($payload);
        $stripeCustomerId = $this->payloadParser->stringValue($subscription['customer'] ?? null);

        if ($stripeCustomerId === null) {
            return $response;
        }

        $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

        if ($lookup['tenant']) {
            Log::info("Tenant {$lookup['tenant']->id} subscription fully canceled");
        }

        return $response;
    }
}
