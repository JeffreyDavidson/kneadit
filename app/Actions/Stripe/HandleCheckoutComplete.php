<?php

namespace App\Actions\Stripe;

use App\Actions\Orders\MarkOrderPaid;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Log;

class HandleCheckoutComplete
{
    public function __construct(
        private MarkOrderPaid $markOrderPaid,
    ) {}

    public function __invoke(Order $order, string $paymentIntentId): Order
    {
        $order->update([
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);

        ($this->markOrderPaid)($order);

        Log::info('Order marked as paid via Stripe', ['order' => $order->order_number]);

        return $order;
    }
}
