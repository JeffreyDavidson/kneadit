<?php

declare(strict_types=1);

namespace App\Services\PayPal;

use App\DataTransferObjects\Settings\SettingValue;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenManager
{
    private readonly string $baseUrl;

    private readonly ?string $clientId;

    private readonly ?string $clientSecret;

    private ?string $accessToken = null;

    public function __construct(SettingsManager $settings)
    {
        $this->clientId = $this->credential($settings->get('paypal_client_id'), config('services.paypal.client_id'));
        $this->clientSecret = $this->credential($settings->get('paypal_client_secret'), config('services.paypal.client_secret'));
        $this->baseUrl = SettingValue::bool($settings->get('paypal_sandbox', '1'), true)
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
     * Whether this bakery has its own PayPal credentials configured. The platform's
     * env credentials count only in the local environment (see credential()), so a
     * bakery never invoices through KneadIt's PayPal account. UI surfaces should hide
     * PayPal-dependent actions when this returns false so users don't click and get
     * a generic auth-failure error.
     */
    public function isConfigured(): bool
    {
        return ! in_array($this->clientId, [null, '', '0'], true) && ! in_array($this->clientSecret, [null, '', '0'], true);
    }

    /**
     * The bakery's own value, or, in the local environment only, the env credential
     * so a developer can try PayPal without filling in settings.
     */
    private function credential(mixed $tenantValue, mixed $localValue): ?string
    {
        if (is_string($tenantValue) && $tenantValue !== '') {
            return $tenantValue;
        }

        if (! app()->environment('local')) {
            return null;
        }

        return is_string($localValue) && $localValue !== '' ? $localValue : null;
    }
}
