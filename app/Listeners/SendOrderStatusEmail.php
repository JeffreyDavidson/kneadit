<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderReady;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if (! $order->customer?->email) {
            return;
        }

        $email = $order->customer->email;

        match ($event->to) {
            OrderStatus::Confirmed => Mail::to($email)->send(new OrderConfirmed($order)),
            OrderStatus::Baking => Mail::to($email)->send(new OrderBaking($order)),
            OrderStatus::Ready => Mail::to($email)->send(new OrderReady($order)),
            OrderStatus::Delivered => Mail::to($email)->send(new OrderDelivered($order)),
            OrderStatus::Cancelled => Mail::to($email)->send(new OrderCancelled($order)),
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
