<?php

namespace App\Actions\Orders;

use App\Actions\Stripe\ExpireStripeCheckout;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Marks an order paid exactly once, however many requests ask at the same time.
 *
 * The Stripe success redirect, the Connect webhook, the PayPal poller and staff
 * can all mark the same order paid together, so the paid check is made again on
 * the locked row. Whichever request commits first does the work; the others find
 * the order already paid and change nothing.
 *
 * When the payment did not come through Stripe (no payment intent recorded), the
 * order's open Checkout session is expired so the customer can't pay it a second time.
 */
class MarkOrderPaid
{
    public function __construct(
        private readonly TransitionOrderStatus $transitionOrderStatus,
        private readonly ExpireStripeCheckout $expireStripeCheckout,
    ) {}

    public function __invoke(Order $order): Order
    {
        $markedPaid = DB::transaction(function () use ($order): bool {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $order->setRawAttributes($locked->getAttributes(), true);

            if ($order->payment_status === PaymentStatus::Paid) {
                return false;
            }

            $order->update(['payment_status' => PaymentStatus::Paid]);

            return true;
        });

        if (! $markedPaid) {
            return $order;
        }

        Log::info('Order marked as paid', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method->value,
            'amount_cents' => $order->total->cents(),
        ]);

        $this->autoConfirm($order);

        if ($order->stripe_payment_intent_id === null) {
            ($this->expireStripeCheckout)($order);
        }

        return $order->refresh();
    }

    private function autoConfirm(Order $order): void
    {
        if (! $this->shouldAutoConfirm($order)) {
            return;
        }

        try {
            ($this->transitionOrderStatus)($order, OrderStatus::Confirmed);
        } catch (InvalidOrderTransitionException) {
            // Another request moved the order on since the paid check, so it is no
            // longer waiting to be confirmed.
        }
    }

    private function shouldAutoConfirm(Order $order): bool
    {
        if ($order->status !== OrderStatus::Pending) {
            return false;
        }

        return ! $order->payment_method->isManual();
    }
}
