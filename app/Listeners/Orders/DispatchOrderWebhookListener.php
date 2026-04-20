<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderStatusChanged;
use App\Listeners\QueuedListener;
use App\Services\Platform\WebhookService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;

class DispatchOrderWebhookListener extends QueuedListener implements ShouldBeUnique
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

    public function handle(OrderStatusChanged $event): void
    {
        $this->webhookService->dispatch('order.updated', [
            'order_number' => $event->order->order_number,
            'status' => $event->to->value,
            'previous_status' => $event->from->value,
            'payment_status' => $event->order->payment_status->value,
            'total' => $event->order->total->dollars(),
        ]);
    }

    public function failed(OrderStatusChanged $event, \Throwable $exception): void
    {
        Log::warning('Order status webhook dispatch failed', [
            'order' => $event->order->order_number,
            'transition' => "{$event->from->value} -> {$event->to->value}",
            'error' => $exception->getMessage(),
        ]);
    }
}
