<?php

namespace App\Exceptions\Stripe;

use App\Models\Orders\Order;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

/**
 * Thrown when Stripe's refunds.create call fails. Wraps the underlying
 * Stripe SDK exception so the caller can react with typed error handling
 * (retry on rate limit, surface to staff on permanent failure, etc.) and
 * so the failure shows up in error tracking with the order context
 * already attached — vs the previous shape that wrapped the SDK error in
 * a generic RuntimeException with the order_id only in a Log::error
 * one-off above the throw.
 *
 * Intentionally NOT marked ShouldntReport: this is an infrastructure
 * failure, not a caller-validation error. We want it on the dashboard.
 */
class StripeRefundFailedException extends RuntimeException
{
    public function __construct(
        public readonly Order $order,
        public readonly string $paymentIntentId,
        public readonly ?string $stripeErrorCode,
        ApiErrorException $previous,
    ) {
        parent::__construct(
            sprintf(
                'Stripe refund failed for order %s (payment intent %s): %s',
                $order->order_number,
                $paymentIntentId,
                $previous->getMessage(),
            ),
            previous: $previous,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'payment_intent_id' => $this->paymentIntentId,
            'stripe_error_code' => $this->stripeErrorCode,
            'stripe_message' => $this->getPrevious()?->getMessage(),
        ];
    }
}
