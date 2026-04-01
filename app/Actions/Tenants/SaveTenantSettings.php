<?php

namespace App\Actions\Tenants;

class SaveTenantSettings
{
    /**
     * @param array<string, mixed> $data
     */
    public function __invoke(array $data): void
    {
        // Store Information
        settings(['store_name' => $data['store_name']]);
        settings(['store_email' => $data['store_email']]);
        settings(['store_phone' => $data['store_phone']]);
        settings(['store_address' => $data['store_address']]);

        // Order Settings
        settings(['default_daily_capacity' => $data['default_daily_capacity']]);
        settings(['minimum_order_lead_hours' => $data['minimum_order_lead_hours']]);
        settings(['delivery_fee_tiers' => $data['delivery_fee_tiers']]);

        // Notification Settings
        settings(['repeat_reminders_enabled' => $data['repeat_reminders_enabled']]);
        settings(['birthday_program_enabled' => $data['birthday_program_enabled']]);

        // Payment Methods
        settings(['payment_methods' => json_encode($data['payment_methods'])]);
        settings(['payment_method' => $data['payment_methods'][0] ?? 'cash']);

        if (in_array('paypal', $data['payment_methods'] ?? [])) {
            settings(['paypal_client_id' => $data['paypal_client_id']]);
            settings(['paypal_client_secret' => $data['paypal_client_secret']]);
            settings(['paypal_sandbox' => $data['paypal_sandbox'] ? '1' : '0']);
            settings(['webhook_url' => $data['webhook_url']]);
            settings(['webhook_secret' => $data['webhook_secret']]);
        }

        // Compliance
        settings(['allergy_disclaimer' => $data['allergy_disclaimer']]);
        settings(['revenue_cap' => $data['revenue_cap']]);
        settings(['cancellation_policy' => $data['cancellation_policy']]);
        settings(['deposit_policy' => $data['deposit_policy']]);
        settings(['refund_policy' => $data['refund_policy']]);
        settings(['pickup_policy' => $data['pickup_policy']]);
        settings(['additional_terms' => $data['additional_terms']]);
        settings(['show_policies_on_storefront' => $data['show_policies_on_storefront'] ? '1' : '0']);
    }
}
