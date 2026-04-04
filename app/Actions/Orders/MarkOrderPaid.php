<?php

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

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
