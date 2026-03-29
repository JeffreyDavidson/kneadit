<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
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
        $secret = config('kneadit.stripe_connect.webhook_secret');

        // Webhook signature verification is mandatory
        if (! $secret) {
            Log::error('STRIPE_CONNECT_WEBHOOK_SECRET not configured');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, (string) $sigHeader, $secret);
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
        if ($eventId && ! Cache::add("stripe_event:{$eventId}", true, now()->addHours(24))) {
            return response('Already processed', 200);
        }

        Log::info('Stripe Connect webhook received', ['type' => $type]);

        match ($type) {
            'account.updated' => $this->handleAccountUpdated($data),
            'checkout.session.completed' => $this->handleCheckoutCompleted($data),
            default => null,
        };

        return response('OK', 200);
    }

    /**
     * When a connected account is updated (e.g., onboarding completed).
     */
    protected function handleAccountUpdated(mixed $account): void
    {
        $accountId = data_get($account, 'id');
        $chargesEnabled = (bool) data_get($account, 'charges_enabled', false);
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
            /** @var Tenant|null $tenant */
            $tenant = Tenant::query()->find($tenantId);
            if (! $tenant) {
                Log::warning('Tenant not found for Stripe Connect update', ['tenant_id' => $tenantId]);

                return;
            }

            tenancy()->initialize($tenant);

            settings(['stripe_connect_charges_enabled' => $chargesEnabled ? '1' : '0']);

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
        $sessionId = data_get($session, 'id');
        $metadata = data_get($session, 'metadata');
        $orderId = data_get($metadata, 'order_id');
        $tenantId = data_get($metadata, 'tenant_id');

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

        /** @var Tenant|null $tenant */
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
            Log::error('Error processing checkout session for tenant', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
