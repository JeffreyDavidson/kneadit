<?php

namespace App\Http\Controllers;

use App\Actions\Stripe\HandleConnectAccountUpdated;
use App\Actions\Stripe\HandleConnectCheckoutCompleted;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
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
            'account.updated' => resolve(HandleConnectAccountUpdated::class)($data),
            'checkout.session.completed' => resolve(HandleConnectCheckoutCompleted::class)($data),
            default => null,
        };

        return response('OK', 200);
    }
}
