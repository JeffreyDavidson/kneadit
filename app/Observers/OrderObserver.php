<?php

namespace App\Observers;

use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderReady;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // Check if status field has changed
        if ($order->wasChanged('status')) {
            $this->sendStatusEmail($order);

            if ($order->status === 'delivered') {
                $this->awardLoyaltyPoints($order);
            }
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

    private function awardLoyaltyPoints(Order $order): void
    {
        if (Setting::get('loyalty_enabled') !== '1') {
            return;
        }

        if (!$order->customer_id) {
            return;
        }

        // Don't double-award
        if (LoyaltyPoint::where('order_id', $order->id)->where('type', 'earned')->exists()) {
            return;
        }

        $pointsPerDollar = (int) Setting::get('loyalty_points_per_dollar', '10');
        $points = (int) floor((float) $order->total * $pointsPerDollar);

        if ($points <= 0) {
            return;
        }

        LoyaltyPoint::create([
            'customer_id' => $order->customer_id,
            'points' => $points,
            'type' => 'earned',
            'description' => "Earned from order #{$order->id}",
            'order_id' => $order->id,
        ]);
    }
}