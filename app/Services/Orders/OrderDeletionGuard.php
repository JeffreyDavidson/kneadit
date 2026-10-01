<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Models\Orders\Order;

/**
 * Determines whether an order may be hard-deleted.
 *
 * Deleting cascades to the order's refunds and messages and detaches its
 * payments and loyalty points, so it is only allowed when nothing financial
 * happened: the order is Pending or Cancelled, its payment status is Unpaid or
 * Cancelled, and it has no refunds. Anything else must be cancelled instead,
 * which keeps the record.
 */
final readonly class OrderDeletionGuard
{
    public function canDelete(Order $order): bool
    {
        if (! $order->status->allowsDeletion()) {
            return false;
        }

        if (! $order->payment_status->allowsDeletion()) {
            return false;
        }

        return ! $order->refunds()->exists();
    }
}
