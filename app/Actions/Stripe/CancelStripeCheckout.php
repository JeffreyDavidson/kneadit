<?php

namespace App\Actions\Stripe;

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;

class CancelStripeCheckout
{
    public function __invoke(Order $order): void
    {
        $order->update([
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }
}
