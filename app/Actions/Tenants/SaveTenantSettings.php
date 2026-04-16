<?php

namespace App\Actions\Tenants;

use App\Enums\Orders\PaymentMethod;
use App\Services\Settings\SettingsManager;

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
            'delivery_fee_tiers' => $data['delivery_fee_tiers'],
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
                fn ($value) => is_string($value) && trim($value) !== '',
            ))),
        ];

        if (in_array(PaymentMethod::PayPal->value, $data['payment_methods'] ?? [])) {
            $settings['paypal_client_id'] = $data['paypal_client_id'];
            $settings['paypal_client_secret'] = $data['paypal_client_secret'];
            $settings['paypal_sandbox'] = $data['paypal_sandbox'] ? '1' : '0';
            $settings['webhook_url'] = $data['webhook_url'];
            $settings['webhook_secret'] = $data['webhook_secret'];
        }

        $this->settings->setMany($settings);
    }
}
