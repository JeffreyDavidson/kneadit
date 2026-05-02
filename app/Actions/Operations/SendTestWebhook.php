<?php

namespace App\Actions\Operations;

use App\Services\Platform\WebhookService;

class SendTestWebhook
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    /**
     * Dispatch a synthetic order.created webhook the baker can use to verify
     * their endpoint is reachable. Goes through the real WebhookService so
     * the result is recorded in webhook_deliveries and visible from the
     * Webhook Deliveries admin resource.
     */
    public function __invoke(): void
    {
        $this->webhookService->dispatch('order.created', [
            'test' => true,
            'order_number' => 'TEST-0001',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.test',
            'total' => 0.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'delivery_date' => now()->addDay()->toDateString(),
            'items' => [
                [
                    'product' => 'Test product',
                    'quantity' => 1,
                    'unit_price' => 0.00,
                ],
            ],
        ]);
    }
}
