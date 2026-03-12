<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Tenancy;

class StripeConnectWebhookController extends Controller
{
    /**
     * Handle Stripe Connect webhook events.
     *
     * This endpoint receives events about connected accounts
     * (separate from the Cashier webhook for platform subscriptions).
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('saas.stripe_connect.webhook_secret');

        // Verify webhook signature if secret is configured
        if ($secret) {
            try {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (\Exception $e) {
                Log::warning('Stripe Connect webhook signature verification failed', [
                    'error' => $e->getMessage(),
                ]);
                return response('Invalid signature', 400);
            }
        } else {
            $event = json_decode($payload);
        }

        $type = is_object($event) ? ($event->type ?? null) : ($event['type'] ?? null);
        $data = is_object($event) ? ($event->data->object ?? null) : ($event['data']['object'] ?? null);

        Log::info('Stripe Connect webhook received', ['type' => $type]);

        match ($type) {
            'account.updated' => $this->handleAccountUpdated($data),
            default => null,
        };

        return response('OK', 200);
    }

    /**
     * When a connected account is updated (e.g., onboarding completed).
     */
    protected function handleAccountUpdated($account)
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
            $tenant = \App\Models\Tenant::find($tenantId);
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
}
