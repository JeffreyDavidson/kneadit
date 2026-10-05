<?php

namespace App\Services\Stripe;

use App\Actions\Platform\ResumeTenant;
use App\Actions\Stripe\ClearSubscriptionPlan;
use App\Actions\Stripe\SyncSubscriptionPlan;
use App\Events\Platform\PaymentFailed;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Queries\Platform\StripeCustomerLookupQuery;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

final readonly class StripeWebhookEventHandler
{
    /** Statuses that make the owner's subscription valid. */
    private const array VALID_STATUSES = [StripeSubscription::STATUS_ACTIVE, StripeSubscription::STATUS_TRIALING];

    /**
     * Statuses a subscription never leaves. Others, such as past_due, are
     * still being retried, so the plan stays while Stripe collects.
     */
    private const array ENDED_STATUSES = [
        StripeSubscription::STATUS_CANCELED,
        StripeSubscription::STATUS_INCOMPLETE_EXPIRED,
        StripeSubscription::STATUS_UNPAID,
    ];

    public function __construct(
        private StripeWebhookPayloadParser $payloadParser,
        private SyncSubscriptionPlan $syncSubscriptionPlan,
        private ClearSubscriptionPlan $clearSubscriptionPlan,
        private ResumeTenant $resumeTenant,
        private StripeCustomerLookupQuery $customerLookup,
    ) {}

    /**
     * A subscription was created or updated. An active or trialing one resumes
     * the owner's bakery; one that has ended resets the plan, and any other
     * syncs the plan from its price.
     *
     * @param  array<string, mixed>  $subscription
     */
    public function handleSubscriptionUpdated(array $subscription): void
    {
        $stripeCustomerId = $this->payloadParser->stringValue($subscription['customer'] ?? null);

        if ($stripeCustomerId === null) {
            return;
        }

        $lookup = $this->customerLookup->find($stripeCustomerId);

        if ($lookup['user'] === null) {
            return;
        }

        if ($lookup['tenant'] === null) {
            Log::warning('Tenant not found for subscription update', ['stripe_customer' => $stripeCustomerId]);

            return;
        }

        $status = $this->payloadParser->stringValue($subscription['status'] ?? null);

        if (in_array($status, self::ENDED_STATUSES, true)) {
            $this->clearPlanUnlessSubscribed($lookup['user'], $lookup['tenant'], $this->payloadParser->stringValue($subscription['id'] ?? null));

            return;
        }

        if (in_array($status, self::VALID_STATUSES, true)) {
            ($this->resumeTenant)($lookup['tenant']);
        }

        $stripePriceId = $this->payloadParser->stringValue(data_get($subscription, 'items.data.0.price.id'));

        if ($stripePriceId === null) {
            return;
        }

        ($this->syncSubscriptionPlan)(
            tenant: $lookup['tenant'],
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

        if ($lookup['user'] === null || $lookup['tenant'] === null) {
            return;
        }

        Log::info("Tenant {$lookup['tenant']->id} subscription fully canceled");

        $this->clearPlanUnlessSubscribed($lookup['user'], $lookup['tenant'], $this->payloadParser->stringValue($subscription['id'] ?? null));
    }

    /**
     * The bakery goes back to its starting plan, but not pausing: the trial-expiry
     * run decides that. An owner who already started another valid subscription
     * keeps their plan when an older one ends.
     */
    private function clearPlanUnlessSubscribed(User $owner, Tenant $tenant, ?string $endedSubscriptionId): void
    {
        $hasOtherValidSubscription = Subscription::query()
            ->where('user_id', $owner->id)
            ->where('type', 'default')
            ->when($endedSubscriptionId !== null, fn ($query) => $query->where('stripe_id', '!=', $endedSubscriptionId))
            ->get()
            ->contains(fn (Subscription $subscription): bool => $subscription->valid());

        if ($hasOtherValidSubscription) {
            return;
        }

        ($this->clearSubscriptionPlan)($tenant);
    }
}
