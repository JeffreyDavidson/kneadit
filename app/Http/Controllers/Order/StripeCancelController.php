<?php

namespace App\Http\Controllers\Order;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class StripeCancelController extends Controller
{
    /**
     * Stripe checkout cancel callback.
     */
    public function __invoke(Order $order): RedirectResponse
    {
        $order->update([
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        return to_route('order.confirmation', $order)
            ->with('warning', 'Payment was not completed. You can pay later or contact the baker.');
    }
}
