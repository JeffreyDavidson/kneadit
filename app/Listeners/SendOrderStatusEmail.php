<?php

namespace App\Listeners;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderReady;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if (! $order->customer?->email) {
            return;
        }

        $email = $order->customer->email;

        try {
            match ($event->to) {
                OrderStatus::Confirmed => Mail::to($email)->send(new OrderConfirmed($order)),
                OrderStatus::Baking => Mail::to($email)->send(new OrderBaking($order)),
                OrderStatus::Ready => Mail::to($email)->send(new OrderReady($order)),
                OrderStatus::Delivered => Mail::to($email)->send(new OrderDelivered($order)),
                OrderStatus::Cancelled => Mail::to($email)->send(new OrderCancelled($order)),
                default => null,
            };
        } catch (\Exception $e) {
            Log::warning('Order status email failed', [
                'order' => $order->order_number,
                'status' => $event->to->value,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
