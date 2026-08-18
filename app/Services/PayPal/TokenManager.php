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
        $this->clientId = $this->credential($settings->get('paypal_client_id'), config('services.paypal.client_id'));
        $this->clientSecret = $this->credential($settings->get('paypal_client_secret'), config('services.paypal.client_secret'));
        $this->baseUrl = config('services.paypal.sandbox', true) === true
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        if ($this->clientId === null || $this->clientSecret === null) {
            return null;
        }

        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(3, 100)
                ->asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $accessToken = $response->json('access_token');

                if (! is_string($accessToken) || $accessToken === '') {
                    return null;
                }

                $this->accessToken = $accessToken;

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

    /**
     * Whether this tenant has PayPal credentials configured (either via
     * tenant settings or env-level config). UI surfaces should hide
     * PayPal-dependent actions when this returns false so users don't
     * click and get a generic auth-failure error.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    private function credential(mixed $tenantValue, mixed $configuredValue): ?string
    {
        if (is_string($tenantValue) && $tenantValue !== '') {
            return $tenantValue;
        }

        return is_string($configuredValue) && $configuredValue !== '' ? $configuredValue : null;
    }
}
