<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;

class DispatchOrderUpdatedWebhook
{
    public function handle(OrderStatusChanged $event): void
    {
        try {
            WebhookService::dispatch('order.updated', [
                'order_number' => $event->order->order_number,
                'status' => $event->to,
                'previous_status' => $event->from,
                'payment_status' => $event->order->payment_status,
                'total' => $event->order->total,
            ]);
        } catch (\Exception $e) {
            Log::warning('Order updated webhook dispatch failed', [
                'order' => $event->order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
