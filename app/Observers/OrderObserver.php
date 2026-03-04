<?php

namespace App\Observers;

use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderReady;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // Check if status field has changed
        if ($order->wasChanged('status')) {
            $this->sendStatusEmail($order);
        }
    }

    private function sendStatusEmail(Order $order): void
    {
        // Only send email if customer has an email address
        if (!$order->customer || !$order->customer->email) {
            return;
        }

        $customerEmail = $order->customer->email;

        match ($order->status) {
            'confirmed' => Mail::to($customerEmail)->send(new OrderConfirmed($order)),
            'baking' => Mail::to($customerEmail)->send(new OrderBaking($order)),
            'ready' => Mail::to($customerEmail)->send(new OrderReady($order)),
            'delivered' => Mail::to($customerEmail)->send(new OrderDelivered($order)),
            'cancelled' => Mail::to($customerEmail)->send(new OrderCancelled($order)),
            default => null
        };
    }
}