<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DispatchOrderUpdatedWebhook implements ShouldQueue
{
    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

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
