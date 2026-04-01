<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\SyncSubscriptionPlan;
use App\Events\Platform\PaymentFailed;
use App\Queries\StripeCustomerLookupQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    /** @param array<string, mixed> $payload */
    protected function alreadyProcessed(array $payload): bool
    {
        $eventId = $payload['id'] ?? null;

        if (! $eventId) {
            return false;
        }

        return ! Cache::add("stripe_event:{$eventId}", true, now()->addHours(24));
    }

    /** @param array<string, mixed> $payload */
    protected function handleCustomerSubscriptionUpdated(array $payload): ?Response
    {
        if ($this->alreadyProcessed($payload)) {
            return null;
        }

        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $subscription = $payload['data']['object'] ?? [];
        $stripeCustomerId = $subscription['customer'] ?? null;
        $stripePriceId = $subscription['items']['data'][0]['price']['id'] ?? null;

        if ($stripeCustomerId && $stripePriceId) {
            $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

            if ($lookup['user']) {
                app(SyncSubscriptionPlan::class)(
                    tenantEmail: $lookup['user']->email,
                    stripePriceId: $stripePriceId,
                    priceMap: array_flip(config('kneadit.stripe_prices', [])),
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

        $invoice = $payload['data']['object'] ?? [];
        $stripeCustomerId = $invoice['customer'] ?? null;

        if (! $stripeCustomerId) {
            return;
        }

        $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

        if (! $lookup['user']) {
            return;
        }

        Log::warning('Payment failed', [
            'tenant' => $lookup['tenant']?->id,
            'email' => $lookup['user']->email,
            'amount' => ($invoice['amount_due'] ?? 0) / 100,
        ]);

        PaymentFailed::dispatch(
            $lookup['user'],
            $lookup['tenant'],
            ($invoice['amount_due'] ?? 0) / 100,
        );
    }

    /** @param array<string, mixed> $payload */
    protected function handleCustomerSubscriptionDeleted(array $payload): ?Response
    {
        if ($this->alreadyProcessed($payload)) {
            return null;
        }

        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $subscription = $payload['data']['object'] ?? [];
        $stripeCustomerId = $subscription['customer'] ?? null;

        $lookup = StripeCustomerLookupQuery::find((string) $stripeCustomerId);

        if ($lookup['tenant']) {
            Log::info("Tenant {$lookup['tenant']->id} subscription fully canceled");
        }

        return $response;
    }
}
