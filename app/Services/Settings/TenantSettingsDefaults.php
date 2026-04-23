<?php

namespace App\Services\Settings;

use App\Enums\Customers\CateringEventType;
use App\Enums\Orders\PaymentMethod;

/**
 * Single source of truth for the default values that back the ManageSettings
 * Filament page. Used both for initial hydration (`loadSettings`) and for the
 * "Reset to defaults" action.
 */
final class TenantSettingsDefaults
{
    /** @return array<string, mixed> */
    public static function all(): array
    {
        return [
            'store_name' => '',
            'store_email' => '',
            'store_phone' => '',
            'store_address' => '',
            'default_daily_capacity' => null,
            'minimum_order_lead_hours' => 48,
            'order_modification_window_minutes' => 0,
            'low_stock_alerts_enabled' => false,
            'delivery_fee_tiers' => '{"0-10": 5.00, "10-25": 3.00, "25+": 0.00}',
            'minimum_pickup_order_amount' => '0',
            'minimum_delivery_order_amount' => '0',
            'repeat_reminders_enabled' => false,
            'birthday_program_enabled' => false,
            // Per-email toggles. All default true for backwards compatibility —
            // existing tenants keep getting every email until they opt one out.
            'email_order_placed_enabled' => true,
            'email_order_confirmed_enabled' => true,
            'email_order_baking_enabled' => true,
            'email_order_ready_enabled' => true,
            'email_order_delivered_enabled' => true,
            'email_order_cancelled_enabled' => true,
            'email_order_message_enabled' => true,
            'email_product_available_enabled' => true,
            'allergy_disclaimer' => 'Please inform us of any allergies or dietary restrictions when placing your order.',
            'revenue_cap' => '250000',
            'payment_methods' => [PaymentMethod::Cash->value],
            'paypal_client_id' => '',
            'paypal_client_secret' => '',
            'paypal_sandbox' => true,
            'webhook_url' => '',
            'webhook_secret' => '',
            'cancellation_policy' => '',
            'deposit_policy' => '',
            'refund_policy' => '',
            'pickup_policy' => '',
            'additional_terms' => '',
            'show_policies_on_storefront' => false,
            'catering_event_types' => CateringEventType::defaultLabels(),
            'gift_card_preset_amounts' => '10,25,50,100',
            'gift_card_default_amount' => 25,
            'order_journey_steps' => config('kneadit.default_journey_steps'),
        ];
    }
}
