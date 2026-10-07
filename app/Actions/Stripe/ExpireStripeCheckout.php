<?php

declare(strict_types=1);

namespace App\Actions\Stripe;

use App\Models\Orders\Order;
use App\Services\Stripe\StripeSettingsReader;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Closes an order's open Stripe Checkout session so it can no longer be paid.
 *
 * Used when the order is cancelled or settled another way, so the customer can't
 * pay a link that no longer matches the order. Stripe refuses to expire a session
 * that is already complete or expired. That is logged and swallowed: if the
 * customer paid in the meantime, the payment lands in HandleCheckoutComplete,
 * which refuses to apply it and tells the baker.
 *
 * The stored session id is kept, so a late payment can still be matched to its order.
 */
class ExpireStripeCheckout
{
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly StripeSettingsReader $settings,
    ) {}

    public function __invoke(Order $order): void
    {
        $sessionId = $order->stripe_checkout_session_id;
        $connectId = $this->settings->connectId();

        if ($sessionId === null || $connectId === null) {
            return;
        }

        try {
            $this->stripe->checkout->sessions->expire($sessionId, [], ['stripe_account' => $connectId]);
        } catch (ApiErrorException $e) {
            Log::warning('Could not expire the Stripe checkout session', [
                'order' => $order->order_number,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
