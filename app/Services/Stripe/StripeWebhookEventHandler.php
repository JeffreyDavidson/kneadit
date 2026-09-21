<?php

namespace App\Services\Stripe;

use App\Actions\Stripe\SyncSubscriptionPlan;
use App\Events\Platform\PaymentFailed;
use App\Queries\Platform\StripeCustomerLookupQuery;
use Illuminate\Support\Facades\Log;

final readonly class StripeWebhookEventHandler
{
    public function __construct(
        private StripeWebhookPayloadParser $payloadParser,
        private SyncSubscriptionPlan $syncSubscriptionPlan,
        private StripeCustomerLookupQuery $customerLookup,
    ) {}

    /** @param array<string, mixed> $subscription */
    public function handleSubscriptionUpdated(array $subscription): void
    {
        $stripeCustomerId = $this->payloadParser->stringValue($subscription['customer'] ?? null);
        $stripePriceId = $this->payloadParser->stringValue(data_get($subscription, 'items.data.0.price.id'));

        if ($stripeCustomerId === null || $stripePriceId === null) {
            return;
        }

        $lookup = $this->customerLookup->find($stripeCustomerId);

        if ($lookup['user'] === null) {
            return;
        }

        ($this->syncSubscriptionPlan)(
            tenantEmail: $lookup['user']->email,
            stripePriceId: $stripePriceId,
            priceMap: $this->payloadParser->priceMap(),
        );
    }

    /** @param array<string, mixed> $invoice */
    public function handleInvoicePaymentFailed(array $invoice): void
    {
        $stripeCustomerId = $this->payloadParser->stringValue($invoice['customer'] ?? null);

        if ($stripeCustomerId === null) {
            return;
        }

        $lookup = $this->customerLookup->find($stripeCustomerId);

        if ($lookup['user'] === null) {
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

    /** @param array<string, mixed> $subscription */
    public function handleSubscriptionDeleted(array $subscription): void
    {
        $stripeCustomerId = $this->payloadParser->stringValue($subscription['customer'] ?? null);

        if ($stripeCustomerId === null) {
            return;
        }

        $lookup = $this->customerLookup->find($stripeCustomerId);

        if ($lookup['tenant']) {
            Log::info("Tenant {$lookup['tenant']->id} subscription fully canceled");
        }
    }
}
