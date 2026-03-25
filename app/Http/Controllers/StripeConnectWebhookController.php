<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Tenancy;
use Stripe\Webhook;

class StripeConnectWebhookController extends Controller
{
    /**
     * Handle Stripe Connect webhook events.
     *
     * This endpoint receives events about connected accounts
     * (separate from the Cashier webhook for platform subscriptions).
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('saas.stripe_connect.webhook_secret');

        // Webhook signature verification is mandatory
        if (! $secret) {
            Log::error('STRIPE_CONNECT_WEBHOOK_SECRET not configured');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::warning('Stripe Connect webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        $type = $event->type;
        $data = $event->data->object ?? null;

        // Idempotency: skip already-processed events
        $eventId = $event->id;
        if ($eventId && Cache::has("stripe_event:{$eventId}")) {
            return response('Already processed', 200);
        }

        Log::info('Stripe Connect webhook received', ['type' => $type]);

        match ($type) {
            'account.updated' => $this->handleAccountUpdated($data),
            'checkout.session.completed' => $this->handleCheckoutCompleted($data),
            default => null,
        };

        if ($eventId) {
            Cache::put("stripe_event:{$eventId}", true, now()->addHours(24));
        }

        return response('OK', 200);
    }

    /**
     * When a connected account is updated (e.g., onboarding completed).
     */
    protected function handleAccountUpdated(mixed $account): void
    {
        $accountId = is_object($account) ? $account->id : ($account['id'] ?? null);
        $chargesEnabled = is_object($account)
            ? ($account->charges_enabled ?? false)
            : ($account['charges_enabled'] ?? false);
        $tenantId = is_object($account)
            ? ($account->metadata->tenant_id ?? null)
            : ($account['metadata']['tenant_id'] ?? null);

        if (! $tenantId) {
            Log::warning('Stripe Connect account.updated missing tenant_id', [
                'account_id' => $accountId,
            ]);

            return;
        }

        Log::info('Stripe Connect account updated', [
            'account_id' => $accountId,
            'tenant_id' => $tenantId,
            'charges_enabled' => $chargesEnabled,
        ]);

        // Initialize tenancy to update the tenant's settings
        try {
            $tenant = Tenant::query()->find($tenantId);
            if (! $tenant) {
                Log::warning('Tenant not found for Stripe Connect update', ['tenant_id' => $tenantId]);

                return;
            }

            tenancy()->initialize($tenant);

            Setting::set('stripe_connect_charges_enabled', $chargesEnabled ? '1' : '0');

            if ($chargesEnabled) {
                Log::info('Stripe Connect fully enabled for tenant', ['tenant_id' => $tenantId]);
            }

            tenancy()->end();
        } catch (\Exception $e) {
            Log::error('Failed to update tenant Stripe Connect status', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * When a checkout session completes on a connected account.
     */
    protected function handleCheckoutCompleted(mixed $session): void
    {
        $sessionId = is_object($session) ? $session->id : ($session['id'] ?? null);
        $metadata = is_object($session) ? ($session->metadata ?? null) : ($session['metadata'] ?? null);
        $orderId = is_object($metadata) ? ($metadata->order_id ?? null) : ($metadata['order_id'] ?? null);
        $tenantId = is_object($metadata) ? ($metadata->tenant_id ?? null) : ($metadata['tenant_id'] ?? null);

        if (! $sessionId) {
            return;
        }

        Log::info('Stripe Connect checkout completed', [
            'session_id' => $sessionId,
            'order_id' => $orderId,
            'tenant_id' => $tenantId,
        ]);

        if (! $tenantId) {
            Log::warning('Stripe Connect checkout.session.completed missing tenant_id', [
                'session_id' => $sessionId,
            ]);

            return;
        }

        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            Log::warning('Tenant not found for checkout session', ['tenant_id' => $tenantId]);

            return;
        }

        try {
            tenancy()->initialize($tenant);

            $order = Order::query()->where('stripe_checkout_session_id', $sessionId)->first();
            if ($order) {
                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                ]);
                Log::info('Order marked paid via webhook', [
                    'order' => $order->order_number,
                    'tenant' => $tenant->id,
                ]);
            }

            tenancy()->end();
        } catch (\Exception $e) {
            tenancy()->end();
            Log::warning('Error processing checkout session for tenant', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
