<?php

namespace App\Services\Stripe;

use App\DataTransferObjects\Settings\SettingValue;
use Illuminate\Support\Facades\Config;

final class StripeWebhookPayloadParser
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function object(array $payload): array
    {
        $data = $payload['data'] ?? null;

        if (! is_array($data)) {
            return [];
        }

        return SettingValue::map($data['object'] ?? null);
    }

    public function stringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    /** @return array<string, string> */
    public function priceMap(): array
    {
        $configuredPrices = Config::array('kneadit.stripe_prices', []);
        $priceMap = [];

        foreach ($configuredPrices as $plan => $priceId) {
            if (is_string($plan) && is_string($priceId)) {
                $priceMap[$priceId] = $plan;
            }
        }

        return $priceMap;
    }
}
