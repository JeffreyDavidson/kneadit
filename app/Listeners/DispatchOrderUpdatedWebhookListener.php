<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;

class DispatchOrderUpdatedWebhookListener implements ShouldBeUnique, ShouldQueue
{
    public int $timeout = 30;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimited('webhooks')];
    }

    public function handle(OrderStatusChanged $event): void
    {
        WebhookService::dispatch('order.updated', [
            'order_number' => $event->order->order_number,
            'status' => $event->to->value,
            'previous_status' => $event->from->value,
            'payment_status' => $event->order->payment_status,
            'total' => $event->order->total,
        ]);
    }

    public function failed(OrderStatusChanged $event, \Throwable $exception): void
    {
        Log::warning('Order updated webhook dispatch failed', [
            'order' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
