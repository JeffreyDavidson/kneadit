<?php

namespace App\Actions\Orders;

use App\Actions\Stripe\ExpireStripeCheckout;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Exceptions\Orders\OrderRefundInProgressException;
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
 * An unpaid order's open Stripe Checkout session is expired so it can't be paid
 * after the cancellation.
 *
 * @throws InvalidOrderTransitionException when the order's status can't move to Cancelled
 * @throws StripeRefundFailedException when Stripe refuses the refund
 * @throws OrderRefundInProgressException when another request is already refunding the order
 */
class CancelOrder
{
    public function __construct(
        private readonly TransitionOrderStatus $transitionOrderStatus,
        private readonly RefundStripePayment $refundStripePayment,
        private readonly ExpireStripeCheckout $expireStripeCheckout,
    ) {}

    public function __invoke(Order $order, ?User $initiatedBy = null, ?string $reason = null): ?Refund
    {
        // Check on a fresh copy so a request holding a stale one (a double tap,
        // or two tabs) is refused before it reaches Stripe. No transaction
        // spans the refund, which calls Stripe: RefundStripePayment claims the
        // refund atomically instead, and the status change re-checks under its
        // own short lock.
        $order->refresh();

        throw_unless(
            in_array(OrderStatus::Cancelled, TransitionOrderStatus::allowedTransitions($order), true),
            InvalidOrderTransitionException::class,
            $order,
            $order->status,
            OrderStatus::Cancelled,
        );

        $refund = ($this->refundStripePayment)($order, $initiatedBy, $reason);

        ($this->transitionOrderStatus)($order, OrderStatus::Cancelled);

        // An order nobody has paid may still have a Checkout link the customer could pay
        // later, so close it. This runs after the status change, outside any transaction.
        if ($order->payment_status === PaymentStatus::Unpaid) {
            ($this->expireStripeCheckout)($order);
        }

        return $refund;
    }
}
