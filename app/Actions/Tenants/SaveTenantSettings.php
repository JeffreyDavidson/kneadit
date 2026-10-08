<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Orders\PaymentMethod;
use App\Models\Platform\Tenant;
use App\Services\Settings\SettingsManager;
use App\Support\PhoneNumber;
use Illuminate\Support\Str;

class SaveTenantSettings
{
    public function __construct(
        private readonly SettingsManager $settings,
    ) {}

    /** @param array<string, mixed> $data */
    public function __invoke(array $data): void
    {
        $deliveryFeeTiers = $this->arrayValue($data['delivery_fee_tiers'] ?? [], 'delivery_fee_tiers');
        $paymentMethods = $this->arrayValue($data['payment_methods'] ?? [], 'payment_methods');
        $orderJourneySteps = $this->arrayValue($data['order_journey_steps'] ?? [], 'order_journey_steps');
        $cateringEventTypes = $this->arrayValue($data['catering_event_types'] ?? [], 'catering_event_types');

        $settings = [
            'store_name' => $data['store_name'],
            'store_email' => $data['store_email'],
            'store_phone' => PhoneNumber::normalize(SettingValue::nullableString($data['store_phone'] ?? null)) ?? '',
            'store_address' => $data['store_address'],
            'store_website' => $data['store_website'] ?? '',
            'store_city' => $data['store_city'] ?? '',
            'store_state' => $data['store_state'] ?? '',
            'store_zip' => $data['store_zip'] ?? '',
            'default_daily_capacity' => $data['default_daily_capacity'],
            'minimum_order_lead_hours' => $data['minimum_order_lead_hours'],
            'timezone' => $data['timezone'] ?? 'UTC',
            'default_shelf_life_days' => SettingValue::string($data['default_shelf_life_days'] ?? null, '3'),
            'delivery_fee_tiers' => json_encode(array_values($deliveryFeeTiers)),
            'minimum_pickup_order_amount' => $data['minimum_pickup_order_amount'] ?? '0',
            'minimum_delivery_order_amount' => $data['minimum_delivery_order_amount'] ?? '0',
            'order_modification_window_minutes' => SettingValue::string($data['order_modification_window_minutes'] ?? null, '0'),
            'pickup_slots_enabled' => ($data['pickup_slots_enabled'] ?? false) ? '1' : '0',
            'pickup_slot_interval_minutes' => SettingValue::string($data['pickup_slot_interval_minutes'] ?? null, '30'),
            'pickup_slot_max_per_window' => SettingValue::string($data['pickup_slot_max_per_window'] ?? null, '3'),
            'sitewide_sale_enabled' => ($data['sitewide_sale_enabled'] ?? false) ? '1' : '0',
            'sitewide_sale_percent' => SettingValue::string($data['sitewide_sale_percent'] ?? null, '0'),
            'sitewide_sale_label' => SettingValue::string($data['sitewide_sale_label'] ?? null, 'Sale'),
            'repeat_reminders_enabled' => $data['repeat_reminders_enabled'],
            'repeat_reminder_days' => SettingValue::string($data['repeat_reminder_days'] ?? null, '30'),
            'birthday_program_enabled' => $data['birthday_program_enabled'],
            'birthday_coupon_enabled' => ($data['birthday_coupon_enabled'] ?? true) ? '1' : '0',
            'birthday_discount_percentage' => SettingValue::string($data['birthday_discount_percentage'] ?? null, '15'),
            'birthday_coupon_valid_days' => SettingValue::string($data['birthday_coupon_valid_days'] ?? null, '7'),
            'review_requests_enabled' => ($data['review_requests_enabled'] ?? false) ? '1' : '0',
            'review_request_delay_hours' => SettingValue::string($data['review_request_delay_hours'] ?? null, '24'),
            'weekly_digest_enabled' => ($data['weekly_digest_enabled'] ?? true) ? '1' : '0',
            'low_stock_alerts_enabled' => ($data['low_stock_alerts_enabled'] ?? false) ? '1' : '0',
            'customer_referral_program_enabled' => ($data['customer_referral_program_enabled'] ?? false) ? '1' : '0',
            'customer_referral_discount_dollars' => SettingValue::string($data['customer_referral_discount_dollars'] ?? null, '10'),
            'abandoned_cart_recovery_enabled' => ($data['abandoned_cart_recovery_enabled'] ?? false) ? '1' : '0',
            'abandoned_cart_recovery_hours' => SettingValue::string($data['abandoned_cart_recovery_hours'] ?? null, '24'),
            'abandoned_cart_recovery_coupon_dollars' => SettingValue::string($data['abandoned_cart_recovery_coupon_dollars'] ?? null, '5'),
            'catering_enabled' => ($data['catering_enabled'] ?? false) ? '1' : '0',
            'catering_minimum_guests' => SettingValue::string($data['catering_minimum_guests'] ?? null, '10'),
            'catering_lead_time_days' => SettingValue::string($data['catering_lead_time_days'] ?? null, '14'),
            'catering_deposit_percent' => SettingValue::string($data['catering_deposit_percent'] ?? null, '25'),
            'loyalty_program_name' => SettingValue::string($data['loyalty_program_name'] ?? null, 'Rewards'),
            'loyalty_points_per_dollar' => SettingValue::string($data['loyalty_points_per_dollar'] ?? null, '10'),
            'loyalty_tiers_enabled' => ($data['loyalty_tiers_enabled'] ?? false) ? '1' : '0',
            'loyalty_tier_silver_threshold' => SettingValue::string($data['loyalty_tier_silver_threshold'] ?? null, '500'),
            'loyalty_tier_gold_threshold' => SettingValue::string($data['loyalty_tier_gold_threshold'] ?? null, '2000'),
            'loyalty_tier_platinum_threshold' => SettingValue::string($data['loyalty_tier_platinum_threshold'] ?? null, '5000'),
            'loyalty_tier_perks_enabled' => ($data['loyalty_tier_perks_enabled'] ?? false) ? '1' : '0',
            'loyalty_tier_silver_multiplier' => SettingValue::string($data['loyalty_tier_silver_multiplier'] ?? null, '1.0'),
            'loyalty_tier_gold_multiplier' => SettingValue::string($data['loyalty_tier_gold_multiplier'] ?? null, '1.5'),
            'loyalty_tier_platinum_multiplier' => SettingValue::string($data['loyalty_tier_platinum_multiplier'] ?? null, '2.0'),
            'loyalty_tier_silver_free_delivery' => ($data['loyalty_tier_silver_free_delivery'] ?? false) ? '1' : '0',
            'loyalty_tier_gold_free_delivery' => ($data['loyalty_tier_gold_free_delivery'] ?? true) ? '1' : '0',
            'loyalty_tier_platinum_free_delivery' => ($data['loyalty_tier_platinum_free_delivery'] ?? true) ? '1' : '0',
            'payment_methods' => json_encode($paymentMethods),
            'payment_method' => $paymentMethods[0] ?? PaymentMethod::Cash->value,
            'allergy_disclaimer' => $data['allergy_disclaimer'],
            'revenue_cap' => $data['revenue_cap'],
            'cancellation_policy' => $data['cancellation_policy'],
            'deposit_policy' => $data['deposit_policy'],
            'refund_policy' => $data['refund_policy'],
            'pickup_policy' => $data['pickup_policy'],
            'additional_terms' => $data['additional_terms'],
            'show_policies_on_storefront' => $data['show_policies_on_storefront'] ? '1' : '0',
            'order_journey_steps' => json_encode(array_values($orderJourneySteps)),
            'catering_event_types' => json_encode(array_values(array_filter(
                $cateringEventTypes,
                fn (mixed $value): bool => is_string($value) && trim($value) !== '',
            ))),
            // Per-status order email toggles. Stored as '1'/'0' strings to
            // match how EngagementSettings reads them (=== '1').
            'email_order_placed_enabled' => ($data['email_order_placed_enabled'] ?? true) ? '1' : '0',
            'email_order_confirmed_enabled' => ($data['email_order_confirmed_enabled'] ?? true) ? '1' : '0',
            'email_order_baking_enabled' => ($data['email_order_baking_enabled'] ?? true) ? '1' : '0',
            'email_order_ready_enabled' => ($data['email_order_ready_enabled'] ?? true) ? '1' : '0',
            'email_order_delivered_enabled' => ($data['email_order_delivered_enabled'] ?? true) ? '1' : '0',
            'email_order_cancelled_enabled' => ($data['email_order_cancelled_enabled'] ?? true) ? '1' : '0',
            'email_order_message_enabled' => ($data['email_order_message_enabled'] ?? true) ? '1' : '0',
            'email_product_available_enabled' => ($data['email_product_available_enabled'] ?? true) ? '1' : '0',
            // Gift card preset amounts (comma-separated string) and default.
            'gift_card_preset_amounts' => $data['gift_card_preset_amounts'] ?? '',
            'gift_card_default_amount' => $data['gift_card_default_amount'] ?? 25,
        ];

        // PayPal credentials are always persisted — the form still sends them
        // even when the section is hidden (Filament preserves property values
        // across visibility toggles). Defaulting to '' here makes the action
        // safe to call programmatically without paypal_* keys present.
        $settings['paypal_client_id'] = $data['paypal_client_id'] ?? '';
        $settings['paypal_client_secret'] = $data['paypal_client_secret'] ?? '';
        $settings['paypal_sandbox'] = ($data['paypal_sandbox'] ?? false) ? '1' : '0';
        $settings['paypal_invoice_terms'] = $data['paypal_invoice_terms'] ?? 'Payment due within 30 days.';

        // Webhooks are independent of payment method. When a URL is set without
        // a secret (first save or after a manual clear), auto-generate one so
        // we never sign with an empty key.
        $webhookUrl = $data['webhook_url'] ?? '';
        $webhookSecret = $data['webhook_secret'] ?? '';

        if ($webhookUrl !== '' && $webhookSecret === '') {
            $webhookSecret = Str::random(40);
        }

        $settings['webhook_url'] = $webhookUrl;
        $settings['webhook_secret'] = $webhookSecret;

        $this->settings->setMany($settings);

        $this->syncCentralStoreName($data['store_name']);
    }

    /**
     * The directory and platform lists read the name from the central tenant row,
     * so a rename in Settings has to update it too (as the onboarding welcome step does).
     */
    private function syncCentralStoreName(mixed $storeName): void
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant || ! is_string($storeName)) {
            return;
        }

        $tenant->store_name = $storeName;
        $tenant->save();
    }

    /** @return array<array-key, mixed> */
    private function arrayValue(mixed $value, string $key): array
    {
        if (! is_array($value)) {
            throw new \UnexpectedValueException("Expected {$key} to be an array.");
        }

        return $value;
    }
}
