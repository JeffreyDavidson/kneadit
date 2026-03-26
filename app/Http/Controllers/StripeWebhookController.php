<?php

namespace App\Http\Controllers;

use App\Mail\HealthAlertMail;
use App\Mail\PaymentFailedMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    /**
     * Check if a Stripe event has already been processed.
     * Returns true if the event should be skipped.
     */
    /** @param array<string, mixed> $payload */
    protected function alreadyProcessed(array $payload): bool
    {
        $eventId = $payload['id'] ?? null;

        if (! $eventId) {
            return false;
        }

        // Atomic check-and-lock: returns false if key already exists
        return ! Cache::add("stripe_event:{$eventId}", true, now()->addHours(24));
    }

    /**
     * Handle customer subscription updated.
     * Syncs plan changes from Stripe to tenant record.
     */
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
        $status = $subscription['status'] ?? null;

        if (! $stripeCustomerId || ! $stripePriceId) {
            return null;
        }

        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return null;
        }

        $tenant = Tenant::query()->where('email', $user->email)->first();
        if (! $tenant) {
            return null;
        }

        // Map Stripe price ID back to plan name
        $plan = $this->priceIdToPlan($stripePriceId);
        if ($plan && $tenant->plan !== $plan) {
            $oldPlan = $tenant->plan;
            $tenant->update(['plan' => $plan]);

            Log::info("Tenant {$tenant->id} plan changed: {$oldPlan} → {$plan}");
        }

        // Handle subscription cancellation (status becomes 'canceled' at period end)
        if ($status === 'canceled' || ($subscription['cancel_at_period_end'] ?? false)) {
            Log::info("Tenant {$tenant->id} subscription canceling at period end");
        }

        return $response;
    }

    /**
     * Handle invoice payment failed.
     * Alerts the baker and platform.
     */
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

        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        $tenant = Tenant::query()->where('email', $user->email)->first();

        Log::warning('Payment failed', [
            'tenant' => $tenant?->id,
            'email' => $user->email,
            'amount' => ($invoice['amount_due'] ?? 0) / 100,
        ]);

        // Notify the baker
        try {
            Mail::to($user->email)->queue(new PaymentFailedMail($user));
        } catch (\Exception $e) {
            Log::error('Failed to send payment failure email', ['error' => $e->getMessage()]);
        }

        // Notify platform
        try {
            $alertMsg = "Payment failed for {$user->name} ({$user->email})"
                . ($tenant ? " — Tenant: {$tenant->store_name} ({$tenant->id})" : '')
                . "\nAmount: $" . number_format(($invoice['amount_due'] ?? 0) / 100, 2);
            Mail::to(config('mail.platform_notify'))->queue(new HealthAlertMail($alertMsg));
        } catch (\Exception $e) {
            Log::error('Failed to send platform payment alert', ['error' => $e->getMessage()]);
        }

    }

    /**
     * Handle customer subscription deleted (fully canceled).
     */
    /** @param array<string, mixed> $payload */
    protected function handleCustomerSubscriptionDeleted(array $payload): ?Response
    {
        if ($this->alreadyProcessed($payload)) {
            return null;
        }

        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $subscription = $payload['data']['object'] ?? [];
        $stripeCustomerId = $subscription['customer'] ?? null;

        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return null;
        }

        $tenant = Tenant::query()->where('email', $user->email)->first();
        if ($tenant) {
            Log::info("Tenant {$tenant->id} subscription fully canceled");
        }

        return $response;
    }

    protected function priceIdToPlan(string $priceId): ?string
    {
        $prices = config('saas.stripe_prices', []);

        foreach ($prices as $plan => $id) {
            if ($id === $priceId) {
                return $plan;
            }
        }

        return null;
    }
}
