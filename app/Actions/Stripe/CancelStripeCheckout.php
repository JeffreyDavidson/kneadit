<?php

namespace App\Actions\Stripe;

use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;

class CancelStripeCheckout
{
    public function __invoke(Order $order): void
    {
        if ($order->payment_status !== PaymentStatus::Unpaid) {
            return;
        }

        $order->update([
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }
}
