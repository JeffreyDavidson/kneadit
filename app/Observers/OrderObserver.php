<?php

namespace App\Observers;

use App\Mail\NewOrderNotification;
use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderPlaced;
use App\Mail\OrderReady;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Setting;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function created(Order $order): void
    {
        try {
            // Send confirmation to customer
            if ($order->customer?->email) {
                Mail::to($order->customer->email)->send(new OrderPlaced($order));
            }

            // Notify baker
            $bakerEmail = Setting::get('store_email');
            if ($bakerEmail) {
                Mail::to($bakerEmail)->send(new NewOrderNotification($order));
            }
        } catch (\Exception $e) {
            Log::warning('Order creation emails failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        // Dispatch webhook
        WebhookService::dispatch('order.created', [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer?->name,
            'customer_email' => $order->customer?->email,
            'total' => $order->total,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'delivery_date' => $order->delivery_date?->toDateString(),
            'items' => ($order->items ?? collect())->map(fn ($item) => [
                'product' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->toArray(),
        ]);
    }

    public function updated(Order $order): void
    {
        // Check if status field has changed
        if ($order->wasChanged('status')) {
            $this->sendStatusEmail($order);

            if ($order->status === 'delivered') {
                $this->awardLoyaltyPoints($order);
            }

            if ($order->status === 'baking') {
                $this->deductIngredients($order);
            }

            WebhookService::dispatch('order.updated', [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'previous_status' => $order->getOriginal('status'),
                'payment_status' => $order->payment_status,
                'total' => $order->total,
            ]);
        }
    }

    private function sendStatusEmail(Order $order): void
    {
        // Only send email if customer has an email address
        if (! $order->customer || ! $order->customer->email) {
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

        if (! $order->customer_id) {
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

    private function deductIngredients(Order $order): void
    {
        $order->loadMissing('orderItems.product.recipes.inventoryIngredients');

        foreach ($order->orderItems as $orderItem) {
            $product = $orderItem->product;
            if (! $product) {
                continue;
            }

            foreach ($product->recipes as $recipe) {
                foreach ($recipe->inventoryIngredients as $ingredient) {
                    $qty = $ingredient->pivot->quantity * $orderItem->quantity;
                    $ingredient->adjustStock(
                        -$qty,
                        'usage',
                        "Order #{$order->order_number}"
                    );
                }
            }
        }
    }
}
