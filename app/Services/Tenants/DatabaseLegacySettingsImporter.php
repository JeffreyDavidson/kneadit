<?php

namespace App\Services\Tenants;

use App\Services\Settings\TenantSettingCipher;
use App\Services\Tenants\Contracts\LegacySettingsImporter;
use Illuminate\Support\Facades\DB;

class DatabaseLegacySettingsImporter implements LegacySettingsImporter
{
    public function __construct(private readonly TenantSettingCipher $settingCipher) {}

    /** @param array<int, array<string, mixed>> $settings */
    public function import(array $settings): void
    {
        $keyMap = [
            'business_name' => 'store_name', 'tagline' => 'store_tagline',
            'default_prep_time_hours' => 'order_lead_time_hours', 'minimum_order_amount' => 'minimum_pickup_order_amount',
            'delivery_radius_miles' => 'delivery_radius', 'send_review_followup_emails' => 'review_requests_enabled',
        ];

        foreach ($settings as $setting) {
            $legacyKey = $this->stringValue($setting['key']);
            $key = $keyMap[$legacyKey] ?? $legacyKey;
            $value = $this->settingCipher->encrypt($key, $this->normalize($key, $setting['value']));
            $this->upsert($key, $value, $setting['created_at'] ?? now());

            if ($legacyKey === 'default_prep_time_hours') {
                $this->upsert('minimum_order_lead_hours', $value, now());
            }
            if ($legacyKey === 'minimum_order_amount') {
                $this->upsert('minimum_delivery_order_amount', $value, now());
            }
        }

        foreach (['storefront_theme' => 'biscotto', 'admin_theme' => 'honey', 'storefront_enabled' => '1'] as $key => $value) {
            $this->upsert($key, $value, now());
        }
    }

    private function normalize(string $key, mixed $value): mixed
    {
        if ($key === 'delivery_fee_tiers' && is_string($value) && ! str_starts_with(trim($value), '[')) {
            $tiers = [];
            foreach (explode(',', $value) as $tier) {
                if (! preg_match('/^(\d+)(?:-(\d+)|\+):(\d+(?:\.\d+)?)$/', trim($tier), $matches)) {
                    continue;
                }
                $minimum = (int) $matches[1];
                $maximum = $matches[2] !== '' ? (int) $matches[2] : 999;
                $tiers[] = ['min_distance' => $minimum, 'max_distance' => $maximum, 'fee' => number_format((float) $matches[3], 2, '.', ''), 'description' => $maximum === 999 ? "Delivery {$minimum}+ miles" : "Delivery {$minimum}–{$maximum} miles"];
            }

            return json_encode($tiers, JSON_THROW_ON_ERROR);
        }
        if ($key === 'operating_hours' && is_string($value) && ! str_starts_with(trim($value), '{')) {
            return json_encode(['monday' => ['open' => '07:00', 'close' => '18:00'], 'tuesday' => ['open' => '07:00', 'close' => '18:00'], 'wednesday' => ['open' => '07:00', 'close' => '18:00'], 'thursday' => ['open' => '07:00', 'close' => '18:00'], 'friday' => ['open' => '07:00', 'close' => '18:00'], 'saturday' => ['open' => '08:00', 'close' => '16:00'], 'sunday' => []], JSON_THROW_ON_ERROR);
        }

        return $value;
    }

    private function upsert(string $key, mixed $value, mixed $createdAt): void
    {
        DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => $createdAt]);
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
