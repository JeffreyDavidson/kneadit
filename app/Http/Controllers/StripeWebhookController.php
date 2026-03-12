<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController;

class StripeWebhookController extends WebhookController
{
    /**
     * Handle customer subscription updated.
     * Syncs plan changes from Stripe to tenant record.
     */
    public function handleCustomerSubscriptionUpdated(array $payload): void
    {
        parent::handleCustomerSubscriptionUpdated($payload);

        $subscription = $payload['data']['object'] ?? [];
        $stripeCustomerId = $subscription['customer'] ?? null;
        $stripePriceId = $subscription['items']['data'][0]['price']['id'] ?? null;
        $status = $subscription['status'] ?? null;

        if (! $stripeCustomerId || ! $stripePriceId) {
            return;
        }

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        $tenant = Tenant::where('email', $user->email)->first();
        if (! $tenant) {
            return;
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
    }

    /**
     * Handle invoice payment failed.
     * Alerts the baker and platform.
     */
    public function handleInvoicePaymentFailed(array $payload): void
    {
        $invoice = $payload['data']['object'] ?? [];
        $stripeCustomerId = $invoice['customer'] ?? null;

        if (! $stripeCustomerId) {
            return;
        }

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        $tenant = Tenant::where('email', $user->email)->first();

        Log::warning('Payment failed', [
            'tenant' => $tenant?->id,
            'email' => $user->email,
            'amount' => ($invoice['amount_due'] ?? 0) / 100,
        ]);

        // Notify the baker
        try {
            Mail::raw(
                "Hi {$user->name},\n\nWe couldn't process your KneadIt subscription payment. " .
                "Please update your payment method to keep your bakery running.\n\n" .
                "Update payment: https://getkneadit.app/billing/portal\n\n— KneadIt",
                function ($m) use ($user) {
                    $m->to($user->email)
                        ->subject('⚠️ Payment failed — action needed')
                        ->from(config('mail.from.address'), 'KneadIt');
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send payment failure email', ['error' => $e->getMessage()]);
        }

        // Notify platform
        try {
            Mail::raw(
                "Payment failed for {$user->name} ({$user->email})" .
                ($tenant ? " — Tenant: {$tenant->store_name} ({$tenant->id})" : '') .
                "\nAmount: $" . number_format(($invoice['amount_due'] ?? 0) / 100, 2),
                function ($m) {
                    $m->to(config('mail.platform_notify', 'jeffrey@getkneadit.app'))
                        ->subject('Payment Failed — ' . now()->format('M j'))
                        ->from(config('mail.from.address'), 'KneadIt Platform');
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send platform payment alert', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle customer subscription deleted (fully canceled).
     */
    public function handleCustomerSubscriptionDeleted(array $payload): void
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $subscription = $payload['data']['object'] ?? [];
        $stripeCustomerId = $subscription['customer'] ?? null;

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        $tenant = Tenant::where('email', $user->email)->first();
        if ($tenant) {
            Log::info("Tenant {$tenant->id} subscription fully canceled");
        }
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
