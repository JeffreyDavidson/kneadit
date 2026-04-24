<?php

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarkOrderPaid
{
    public function __construct(
        private TransitionOrderStatus $transitionOrderStatus,
    ) {}

    public function __invoke(Order $order): Order
    {
        if ($order->payment_status === PaymentStatus::Paid) {
            return $order;
        }

        DB::transaction(function () use ($order) {
            $order->update(['payment_status' => PaymentStatus::Paid]);
        });

        Log::info('Order marked as paid', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method->value,
            'amount_cents' => $order->total->cents(),
        ]);

        if ($this->shouldAutoConfirm($order)) {
            ($this->transitionOrderStatus)($order, OrderStatus::Confirmed);
        }

        return $order->refresh();
    }

    private function shouldAutoConfirm(Order $order): bool
    {
        if ($order->status !== OrderStatus::Pending) {
            return false;
        }

        return ! $order->payment_method->isManual();
    }
}
