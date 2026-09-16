<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\Stripe\StripeConnectWebhookEventDispatcher;
use App\Services\Stripe\StripeWebhookIdempotency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeConnectWebhookController extends Controller
{
    /**
     * Handle Stripe Connect webhook events.
     *
     * This endpoint receives events about connected accounts
     * (separate from the Cashier webhook for platform subscriptions).
     */
    public function __invoke(
        Request $request,
        StripeWebhookIdempotency $idempotency,
        StripeConnectWebhookEventDispatcher $dispatcher,
    ): Response {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $configuredSecret = Config::get('kneadit.stripe_connect.webhook_secret');
        $secret = is_string($configuredSecret) ? $configuredSecret : '';

        // Webhook signature verification is mandatory
        if (! $secret) {
            Log::error('STRIPE_CONNECT_WEBHOOK_SECRET not configured');

            return response('Webhook secret not configured', 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader ?? '', $secret);
        } catch (\Exception $e) {
            Log::warning('Stripe Connect webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        $type = $event->type;
        $data = $event->data->object ?? null;

        if (! $idempotency->claim($event->id)) {
            return response('Already processed', 200);
        }

        Log::info('Stripe Connect webhook received', [
            'type' => $type,
        ]);

        try {
            $dispatcher->dispatch($type, $data);

            $idempotency->complete($event->id);
        } catch (\Throwable $e) {
            $idempotency->release($event->id);

            Log::error('Stripe Connect webhook processing failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response('Webhook processing failed', 500);
        }

        return response('OK', 200);
    }
}
