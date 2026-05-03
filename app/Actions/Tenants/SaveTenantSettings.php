<?php

namespace App\Actions\Tenants;

use App\Enums\Orders\PaymentMethod;
use App\Services\Settings\SettingsManager;
use Illuminate\Support\Str;

class SaveTenantSettings
{
    public function __construct(
        private SettingsManager $settings,
    ) {}

    /** @param array<string, mixed> $data */
    public function __invoke(array $data): void
    {
        $settings = [
            'store_name' => $data['store_name'],
            'store_email' => $data['store_email'],
            'store_phone' => $data['store_phone'],
            'store_address' => $data['store_address'],
            'default_daily_capacity' => $data['default_daily_capacity'],
            'minimum_order_lead_hours' => $data['minimum_order_lead_hours'],
            'delivery_fee_tiers' => json_encode(array_values($data['delivery_fee_tiers'] ?? [])),
            'minimum_pickup_order_amount' => $data['minimum_pickup_order_amount'] ?? '0',
            'minimum_delivery_order_amount' => $data['minimum_delivery_order_amount'] ?? '0',
            'repeat_reminders_enabled' => $data['repeat_reminders_enabled'],
            'birthday_program_enabled' => $data['birthday_program_enabled'],
            'payment_methods' => json_encode($data['payment_methods']),
            'payment_method' => $data['payment_methods'][0] ?? PaymentMethod::Cash->value,
            'allergy_disclaimer' => $data['allergy_disclaimer'],
            'revenue_cap' => $data['revenue_cap'],
            'cancellation_policy' => $data['cancellation_policy'],
            'deposit_policy' => $data['deposit_policy'],
            'refund_policy' => $data['refund_policy'],
            'pickup_policy' => $data['pickup_policy'],
            'additional_terms' => $data['additional_terms'],
            'show_policies_on_storefront' => $data['show_policies_on_storefront'] ? '1' : '0',
            'order_journey_steps' => json_encode(array_values($data['order_journey_steps'] ?? [])),
            'catering_event_types' => json_encode(array_values(array_filter(
                $data['catering_event_types'] ?? [],
                fn (mixed $value) => is_string($value) && trim($value) !== '',
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
    }
}
