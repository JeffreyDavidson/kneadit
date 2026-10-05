<?php

declare(strict_types=1);

namespace App\Exceptions\Orders;

use App\Models\Orders\Order;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

/** Thrown when another request has already claimed an order's refund and is still talking to Stripe. */
class OrderRefundInProgressException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly Order $order)
    {
        parent::__construct("Order {$order->order_number} is already being refunded.");
    }
}
