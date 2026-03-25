<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch a webhook event to the baker's configured URL.
     */
    /** @param array<string, mixed> $payload */
    public static function dispatch(string $event, array $payload): void
    {
        $url = Setting::get('webhook_url');

        if (! $url) {
            return;
        }

        $secret = Setting::get('webhook_secret', '');

        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $json = json_encode($body);
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
