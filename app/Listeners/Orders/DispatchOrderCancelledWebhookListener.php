<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCancelled;
use App\Listeners\QueuedListener;
use App\Services\Platform\WebhookService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;

class DispatchOrderCancelledWebhookListener extends QueuedListener implements ShouldBeUnique
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

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        $this->webhookService->dispatch('order.cancelled', [
            'order_number' => $order->order_number,
            'previous_status' => $event->from->value,
            'customer_name' => $order->customer?->name,
            'customer_email' => $order->customer?->email,
            'total' => $order->total->dollars(),
            'cancelled_at' => $order->updated_at?->toIso8601String(),
        ]);
    }

    public function failed(OrderCancelled $event, \Throwable $exception): void
    {
        Log::warning('Order cancelled webhook dispatch failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
