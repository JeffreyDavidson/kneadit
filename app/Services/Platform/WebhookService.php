<?php

namespace App\Services\Platform;

use App\DataTransferObjects\Settings\WebhookSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function __construct(
        private WebhookSettings $webhooks,
    ) {}

    /**
     * Dispatch a webhook event to the baker's configured URL.
     *
     * @param array<string, mixed> $payload
     */
    public function dispatch(string $event, array $payload): void
    {
        if (! $this->webhooks->isConfigured()) {
            return;
        }

        $url = $this->webhooks->url;

        if ($url === null) {
            return;
        }

        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $json = json_encode($body) ?: '';
        $signature = hash_hmac('sha256', $json, $this->webhooks->secret);

        try {
            $response = Http::connectTimeout(3)
                ->timeout(5)
                ->retry(2, 100)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-KneadIt-Event' => $event,
                    'X-KneadIt-Signature' => $signature,
                ])
                ->withBody($json, 'application/json')
                ->post($url);

            if ($response->failed()) {
                Log::warning("Webhook returned {$response->status()} for {$event}", [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Webhook dispatch failed for {$event}", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
