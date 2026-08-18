<?php

namespace App\Actions\Operations;

use App\Models\Operations\WebhookDelivery;
use App\Services\Platform\WebhookService;

class RedeliverWebhook
{
    public function __construct(
        private WebhookService $webhookService,
    ) {}

    /**
     * Re-fire a previous webhook delivery using its original payload.
     * Creates a new WebhookDelivery row via the service — does not touch
     * the original record.
     */
    public function __invoke(WebhookDelivery $delivery): void
    {
        $payload = $delivery->payload['data'] ?? [];

        $this->webhookService->dispatch($delivery->event, $payload);
    }
}
