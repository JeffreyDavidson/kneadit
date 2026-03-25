<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\OrderItem;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;

class DispatchOrderCreatedWebhook
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        try {
            $order->loadMissing('orderItems.product');

            WebhookService::dispatch('order.created', [
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->name,
                'customer_email' => $order->customer?->email,
                'total' => $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'delivery_date' => $order->delivery_date?->toDateString(),
                'items' => $order->orderItems->map(fn (OrderItem $item) => [
                    'product' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Order created webhook dispatch failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
