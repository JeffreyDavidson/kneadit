<?php

namespace App\Http\Controllers\Order;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;

class StripeCancelController extends Controller
{
    /**
     * Stripe checkout cancel callback.
     */
    public function __invoke(Order $order)
    {
        $order->update(['payment_status' => PaymentStatus::Unpaid]);

        return to_route('order.confirmation', $order)
            ->with('warning', 'Payment was not completed. You can pay later or contact the baker.');
    }
}
