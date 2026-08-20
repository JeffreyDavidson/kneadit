<?php

namespace App\Http\Controllers\Stripe;

use App\Actions\Stripe\SyncSubscriptionPlan;
use App\DataTransferObjects\Settings\SettingValue;
use App\Events\Platform\PaymentFailed;
use App\Http\Controllers\Stripe\Concerns\EnsuresWebhookIdempotency;
use App\Queries\Platform\StripeCustomerLookupQuery;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    use EnsuresWebhookIdempotency;

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

        $subscription = $this->stripeObject($payload);
        $stripeCustomerId = $this->stringValue($subscription['customer'] ?? null);
        $stripePriceId = $this->stringValue(data_get($subscription, 'items.data.0.price.id'));

        if ($stripeCustomerId && $stripePriceId) {
            $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

            if ($lookup['user']) {
                resolve(SyncSubscriptionPlan::class)(
                    tenantEmail: $lookup['user']->email,
                    stripePriceId: $stripePriceId,
                    priceMap: $this->stripePriceMap(),
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

        $invoice = $this->stripeObject($payload);
        $stripeCustomerId = $this->stringValue($invoice['customer'] ?? null);

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

        $subscription = $this->stripeObject($payload);
        $stripeCustomerId = $this->stringValue($subscription['customer'] ?? null);

        if ($stripeCustomerId === null) {
            return $response;
        }

        $lookup = StripeCustomerLookupQuery::find($stripeCustomerId);

        if ($lookup['tenant']) {
            Log::info("Tenant {$lookup['tenant']->id} subscription fully canceled");
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function stripeObject(array $payload): array
    {
        $data = $payload['data'] ?? null;

        if (! is_array($data)) {
            return [];
        }

        $object = $data['object'] ?? null;

        return SettingValue::map($object);
    }

    private function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    /** @return array<string, string> */
    private function stripePriceMap(): array
    {
        $configuredPrices = Config::array('kneadit.stripe_prices', []);

        $priceMap = [];

        foreach ($configuredPrices as $plan => $priceId) {
            if (is_string($plan) && is_string($priceId)) {
                $priceMap[$priceId] = $plan;
            }
        }

        return $priceMap;
    }
}
