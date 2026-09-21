<?php

namespace App\Filament\Pages\Settings;

use App\DataTransferObjects\Settings\SettingValue;
use App\Enums\Orders\PaymentMethod;

final class TenantSettingsFormMapper
{
    /**
     * Convert persisted settings into the public property names used by the
     * Livewire form. Defaults are applied here so the page remains an adapter.
     *
     * @param  array<string, mixed>  $values
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    public function fromSettings(array $values, array $defaults): array
    {
        return [
            'store_name' => SettingValue::string($values['store_name'] ?? null, SettingValue::string($defaults['store_name'] ?? null)),
            'store_email' => SettingValue::string($values['store_email'] ?? null, SettingValue::string($defaults['store_email'] ?? null)),
            'store_phone' => SettingValue::string($values['store_phone'] ?? null, SettingValue::string($defaults['store_phone'] ?? null)),
            'store_address' => SettingValue::string($values['store_address'] ?? null, SettingValue::string($defaults['store_address'] ?? null)),
            'default_daily_capacity' => SettingValue::nullableInt($values['default_daily_capacity'] ?? null, SettingValue::nullableInt($defaults['default_daily_capacity'] ?? null)),
            'minimum_order_lead_hours' => SettingValue::nullableInt($values['minimum_order_lead_hours'] ?? null, SettingValue::nullableInt($defaults['minimum_order_lead_hours'] ?? null, 48)),
            'delivery_fee_tiers' => SettingValue::mapList($values['delivery_fee_tiers'] ?? null),
            'minimum_pickup_order_amount' => SettingValue::string($values['minimum_pickup_order_amount'] ?? null, SettingValue::string($defaults['minimum_pickup_order_amount'] ?? null, '0')),
            'minimum_delivery_order_amount' => SettingValue::string($values['minimum_delivery_order_amount'] ?? null, SettingValue::string($defaults['minimum_delivery_order_amount'] ?? null, '0')),
            'repeat_reminders_enabled' => SettingValue::bool($values['repeat_reminders_enabled'] ?? null, SettingValue::bool($defaults['repeat_reminders_enabled'] ?? null)),
            'birthday_program_enabled' => SettingValue::bool($values['birthday_program_enabled'] ?? null, SettingValue::bool($defaults['birthday_program_enabled'] ?? null)),
            'email_order_placed_enabled' => SettingValue::bool($values['email_order_placed_enabled'] ?? null, true),
            'email_order_confirmed_enabled' => SettingValue::bool($values['email_order_confirmed_enabled'] ?? null, true),
            'email_order_baking_enabled' => SettingValue::bool($values['email_order_baking_enabled'] ?? null, true),
            'email_order_ready_enabled' => SettingValue::bool($values['email_order_ready_enabled'] ?? null, true),
            'email_order_delivered_enabled' => SettingValue::bool($values['email_order_delivered_enabled'] ?? null, true),
            'email_order_cancelled_enabled' => SettingValue::bool($values['email_order_cancelled_enabled'] ?? null, true),
            'email_order_message_enabled' => SettingValue::bool($values['email_order_message_enabled'] ?? null, true),
            'email_product_available_enabled' => SettingValue::bool($values['email_product_available_enabled'] ?? null, true),
            'allergy_disclaimer' => SettingValue::string($values['allergy_disclaimer'] ?? null, SettingValue::string($defaults['allergy_disclaimer'] ?? null)),
            'revenue_cap' => SettingValue::string($values['revenue_cap'] ?? null, SettingValue::string($defaults['revenue_cap'] ?? null, '250000')),
            'payment_methods' => $this->withListFallback(SettingValue::stringList($values['payment_methods'] ?? null), SettingValue::stringList($defaults['payment_methods'] ?? [PaymentMethod::Cash->value])),
            'paypal_client_id' => SettingValue::string($values['paypal_client_id'] ?? null, SettingValue::string($defaults['paypal_client_id'] ?? null)),
            'paypal_client_secret' => SettingValue::string($values['paypal_client_secret'] ?? null, SettingValue::string($defaults['paypal_client_secret'] ?? null)),
            'paypal_sandbox' => SettingValue::bool($values['paypal_sandbox'] ?? null, true),
            'webhook_url' => SettingValue::string($values['webhook_url'] ?? null, SettingValue::string($defaults['webhook_url'] ?? null)),
            'webhook_secret' => SettingValue::string($values['webhook_secret'] ?? null, SettingValue::string($defaults['webhook_secret'] ?? null)),
            'cancellation_policy' => SettingValue::string($values['cancellation_policy'] ?? null, SettingValue::string($defaults['cancellation_policy'] ?? null)),
            'deposit_policy' => SettingValue::string($values['deposit_policy'] ?? null, SettingValue::string($defaults['deposit_policy'] ?? null)),
            'refund_policy' => SettingValue::string($values['refund_policy'] ?? null, SettingValue::string($defaults['refund_policy'] ?? null)),
            'pickup_policy' => SettingValue::string($values['pickup_policy'] ?? null, SettingValue::string($defaults['pickup_policy'] ?? null)),
            'additional_terms' => SettingValue::string($values['additional_terms'] ?? null, SettingValue::string($defaults['additional_terms'] ?? null)),
            'show_policies_on_storefront' => SettingValue::bool($values['show_policies_on_storefront'] ?? null, SettingValue::bool($defaults['show_policies_on_storefront'] ?? null)),
            'catering_event_types' => $this->withListFallback(SettingValue::stringList($values['catering_event_types'] ?? null), SettingValue::stringList($defaults['catering_event_types'] ?? [])),
            'gift_card_preset_amounts' => SettingValue::string($values['gift_card_preset_amounts'] ?? null, SettingValue::string($defaults['gift_card_preset_amounts'] ?? null)),
            'gift_card_default_amount' => SettingValue::nullableInt($values['gift_card_default_amount'] ?? null, SettingValue::nullableInt($defaults['gift_card_default_amount'] ?? null, 25)),
            'order_journey_steps' => $this->journeySteps($values['order_journey_steps'] ?? null, $defaults['order_journey_steps'] ?? []),
        ];
    }

    /**
     * @param  list<string>  $values
     * @param  list<string>  $defaults
     * @return list<string>
     */
    private function withListFallback(array $values, array $defaults): array
    {
        return $values !== [] ? $values : $defaults;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function journeySteps(mixed $value, mixed $default): array
    {
        $steps = SettingValue::stringMapList($value);

        return $steps !== [] ? $steps : SettingValue::stringMapList($default);
    }
}
