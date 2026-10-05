<?php

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;

/**
 * The one way staff cancel an order. A paid Stripe order is refunded first and
 * only cancelled once Stripe has accepted the refund, so a refused refund
 * leaves the order exactly as it was.
 *
 * Paid orders taken any other way (PayPal, cash) are cancelled but stay Paid,
 * since the money has to be handed back outside the app. Who may cancel a paid
 * order is decided by OrderPolicy::cancel, not here.
 *
 * @throws InvalidOrderTransitionException when the order's status can't move to Cancelled
 * @throws StripeRefundFailedException when Stripe refuses the refund
 */
class CancelOrder
{
    public function __construct(
        private readonly TransitionOrderStatus $transitionOrderStatus,
        private readonly RefundStripePayment $refundStripePayment,
    ) {}

    public function __invoke(Order $order, ?User $initiatedBy = null, ?string $reason = null): ?Refund
    {
        throw_unless(
            in_array(OrderStatus::Cancelled, TransitionOrderStatus::allowedTransitions($order), true),
            InvalidOrderTransitionException::class,
            $order,
            $order->status,
            OrderStatus::Cancelled,
        );

        $refund = ($this->refundStripePayment)($order, $initiatedBy, $reason);

        ($this->transitionOrderStatus)($order, OrderStatus::Cancelled);

        return $refund;
    }
}
