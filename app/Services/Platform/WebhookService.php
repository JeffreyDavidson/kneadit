<?php

namespace App\Services\Platform;

use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function __construct(
        private SettingsManager $settings,
    ) {}

    /**
     * Dispatch a webhook event to the baker's configured URL.
     */
    /** @param array<string, mixed> $payload */
    public function dispatch(string $event, array $payload): void
    {
        $url = $this->settings->get('webhook_url');

        if (! $url) {
            return;
        }

        $secret = $this->settings->get('webhook_secret', '');

        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $json = json_encode($body) ?: '';
        $signature = hash_hmac('sha256', $json, $secret);

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
