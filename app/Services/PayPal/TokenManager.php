<?php

namespace App\Services\PayPal;

use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenManager
{
    private string $baseUrl;

    private ?string $clientId;

    private ?string $clientSecret;

    private ?string $accessToken = null;

    public function __construct(SettingsManager $settings)
    {
        $this->clientId = $settings->get('paypal_client_id') ?: config('services.paypal.client_id');
        $this->clientSecret = $settings->get('paypal_client_secret') ?: config('services.paypal.client_secret');
        $this->baseUrl = config('services.paypal.sandbox', true)
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)
                ->asForm()
                ->withBasicAuth((string) $this->clientId, (string) $this->clientSecret)
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');

                return $this->accessToken;
            }

            Log::error('Failed to get PayPal access token', ['status' => $response->status()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal authentication error', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
