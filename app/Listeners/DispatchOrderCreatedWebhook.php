<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\OrderItem;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DispatchOrderCreatedWebhook implements ShouldQueue
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
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
    }

    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::warning('Order created webhook dispatch failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
