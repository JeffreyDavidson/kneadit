<?php

namespace App\Observers\Orders;

use App\Actions\Orders\GenerateOrderNumber;
use App\Actions\Orders\ReverseOrderDiscounts;
use App\Models\Orders\Order;

class OrderObserver
{
    public function __construct(
        private readonly GenerateOrderNumber $generateOrderNumber,
        private readonly ReverseOrderDiscounts $reverseOrderDiscounts,
    ) {}

    public function creating(Order $order): void
    {
        if (! $order->order_number) {
            $order->order_number = ($this->generateOrderNumber)();
        }
    }

    /**
     * Give back the coupon use and gift card draw before the order disappears,
     * the same as cancelling would. The reversal is idempotent, so an order
     * that was already cancelled is not reversed twice. Runs in the model
     * event so every delete path (including bulk actions) goes through it.
     */
    public function deleting(Order $order): void
    {
        $order->loadMissing(['coupon', 'giftCard']);

        ($this->reverseOrderDiscounts)($order, "Order deleted (was {$order->status->value})");
    }
}
