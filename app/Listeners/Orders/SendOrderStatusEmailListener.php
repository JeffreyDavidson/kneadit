<?php

namespace App\Listeners\Orders;

use App\Enums\OrderStatus;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\QueuedListener;
use App\Mail\Orders\OrderBakingMail;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderDeliveredMail;
use App\Mail\Orders\OrderReadyMail;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmailListener extends QueuedListener implements ShouldBeUnique
{
    public function uniqueId(): string
    {
        return 'order-status-email';
    }

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $order->loadMissing('orderItems.product');

        if (! $order->customer?->email) {
            return;
        }

        $email = $order->customer->email;

        match ($event->to) {
            OrderStatus::Confirmed => Mail::to($email)->send(new OrderConfirmedMail($order)),
            OrderStatus::Baking => Mail::to($email)->send(new OrderBakingMail($order)),
            OrderStatus::Ready => Mail::to($email)->send(new OrderReadyMail($order)),
            OrderStatus::Delivered => Mail::to($email)->send(new OrderDeliveredMail($order)),
            OrderStatus::Cancelled => Mail::to($email)->send(new OrderCancelledMail($order)),
            default => null,
        };
    }

    public function failed(OrderStatusChanged $event, \Throwable $exception): void
    {
        Log::warning('Order status email failed', [
            'order' => $event->order->order_number,
            'status' => $event->to->value,
            'error' => $exception->getMessage(),
        ]);
    }
}
