<?php

namespace App\Services\Platform;

use App\DataTransferObjects\Settings\WebhookSettings;
use App\Models\Operations\WebhookDelivery;
use App\Rules\SafeWebhookUrl;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Uri;

class WebhookService
{
    public function __construct(
        private WebhookSettings $webhooks,
        private SafeWebhookUrl $safeWebhookUrl,
    ) {}

    /**
     * Dispatch a webhook event to the baker's configured URL.
     *
     * Records every attempt to webhook_deliveries so the baker can debug
     * failures from the admin UI.
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

        $publicAddress = $this->safeWebhookUrl->publicAddressFor($url);

        if ($publicAddress === null) {
            Log::warning("Webhook dispatch blocked for {$event}: destination is not a public HTTPS URL.");

            return;
        }

        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $json = json_encode($body) ?: '';
        $signature = hash_hmac('sha256', $json, $this->webhooks->secret);

        $delivery = WebhookDelivery::create([
            'event' => $event,
            'url' => $url,
            'payload' => $body,
            'signature' => $signature,
            'attempt' => 1,
            'succeeded' => false,
            'dispatched_at' => now(),
        ]);

        try {
            $response = Http::connectTimeout(3)
                ->timeout(5)
                ->retry(2, 100, throw: false)
                ->withOptions([
                    'allow_redirects' => false,
                    'curl' => [CURLOPT_RESOLVE => [$this->curlResolveEntry($url, $publicAddress)]],
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-KneadIt-Event' => $event,
                    'X-KneadIt-Signature' => $signature,
                ])
                ->withBody($json, 'application/json')
                ->post($url);

            $this->recordResponse($delivery, $response);

            if ($response->failed()) {
                Log::warning("Webhook returned {$response->status()} for {$event}", [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            $this->recordError($delivery, $e);

            Log::warning("Webhook dispatch failed for {$event}", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function recordResponse(WebhookDelivery $delivery, Response $response): void
    {
        $delivery->update([
            'status_code' => $response->status(),
            'response_body' => mb_substr((string) $response->body(), 0, 2000),
            'succeeded' => $response->successful(),
            'responded_at' => now(),
        ]);
    }

    private function curlResolveEntry(string $url, string $publicAddress): string
    {
        $uri = Uri::of($url);
        $host = $uri->host();
        $port = $uri->port() ?? 443;
        $address = str_contains($publicAddress, ':') ? "[{$publicAddress}]" : $publicAddress;

        return "{$host}:{$port}:{$address}";
    }

    private function recordError(WebhookDelivery $delivery, \Throwable $e): void
    {
        $delivery->update([
            'succeeded' => false,
            'error' => $e->getMessage(),
            'responded_at' => now(),
        ]);
    }
}
