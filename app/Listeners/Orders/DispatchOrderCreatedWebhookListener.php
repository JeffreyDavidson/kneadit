<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Listeners\QueuedListener;
use App\Models\Orders\OrderItem;
use App\Services\Platform\WebhookService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;

class DispatchOrderCreatedWebhookListener extends QueuedListener implements ShouldBeUnique
{
    public int $timeout = 30;

    public function __construct(
        private WebhookService $webhookService,
    ) {}

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimited('webhooks')];
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->loadMissing('orderItems.product');

        $this->webhookService->dispatch('order.created', [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer?->name,
            'customer_email' => $order->customer?->email,
            'total' => $order->total->dollars(),
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'delivery_date' => $order->delivery_date?->toDateString(),
            'items' => $order->orderItems->map(fn (OrderItem $item) => [
                'product' => $item->product?->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price->dollars(),
            ])->all(),
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
