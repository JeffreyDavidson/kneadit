<?php

declare(strict_types=1);

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
            'store_website' => '',
            'store_city' => '',
            'store_state' => '',
            'store_zip' => '',
            'default_daily_capacity' => null,
            'minimum_order_lead_hours' => 48,
            'timezone' => 'UTC',
            'default_shelf_life_days' => 3,
            'order_modification_window_minutes' => 0,
            'low_stock_alerts_enabled' => false,
            'pickup_slots_enabled' => false,
            'pickup_slot_interval_minutes' => 30,
            'pickup_slot_max_per_window' => 3,
            'sitewide_sale_enabled' => false,
            'sitewide_sale_percent' => 0,
            'sitewide_sale_label' => 'Sale',
            'customer_referral_program_enabled' => false,
            'customer_referral_discount_dollars' => 10,
            'abandoned_cart_recovery_enabled' => false,
            'abandoned_cart_recovery_hours' => 24,
            'abandoned_cart_recovery_coupon_dollars' => 5,
            'low_review_alert_threshold' => 2,
            'loyalty_program_name' => 'Rewards',
            'loyalty_points_per_dollar' => 10,
            'loyalty_tiers_enabled' => false,
            'loyalty_tier_silver_threshold' => 500,
            'loyalty_tier_gold_threshold' => 2000,
            'loyalty_tier_platinum_threshold' => 5000,
            'loyalty_tier_perks_enabled' => false,
            'loyalty_tier_silver_multiplier' => '1.0',
            'loyalty_tier_silver_free_delivery' => false,
            'loyalty_tier_gold_multiplier' => '1.5',
            'loyalty_tier_gold_free_delivery' => true,
            'loyalty_tier_platinum_multiplier' => '2.0',
            'loyalty_tier_platinum_free_delivery' => true,
            'delivery_fee_tiers' => '[]',
            'minimum_pickup_order_amount' => '0',
            'minimum_delivery_order_amount' => '0',
            'repeat_reminders_enabled' => false,
            'repeat_reminder_days' => 30,
            'birthday_program_enabled' => false,
            'birthday_coupon_enabled' => true,
            'birthday_discount_percentage' => 15,
            'birthday_coupon_valid_days' => 7,
            'review_requests_enabled' => false,
            'review_request_delay_hours' => 24,
            'weekly_digest_enabled' => true,
            'catering_enabled' => false,
            'catering_minimum_guests' => 10,
            'catering_lead_time_days' => 14,
            'catering_deposit_percent' => 25,
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
            'paypal_invoice_terms' => 'Payment due within 30 days.',
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
