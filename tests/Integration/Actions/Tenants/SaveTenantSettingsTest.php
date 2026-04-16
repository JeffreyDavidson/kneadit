<?php

use App\Actions\Tenants\SaveTenantSettings;

beforeEach(fn () => setUpTenantTest());

test('saves store information settings', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => '[]',
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'allergy_disclaimer' => 'Please notify us of allergies.',
        'revenue_cap' => '250000',
        'cancellation_policy' => 'No refunds.',
        'deposit_policy' => '50% deposit required.',
        'refund_policy' => 'No refunds.',
        'pickup_policy' => 'Pickup within 24 hours.',
        'additional_terms' => 'None.',
        'show_policies_on_storefront' => true,
    ];

    app(SaveTenantSettings::class)($data);

    expect(settings('store_name'))->toBe('Test Bakery')
        ->and(settings('store_email'))->toBe('info@test.com')
        ->and(settings('store_phone'))->toBe('555-1234')
        ->and(settings('store_address'))->toBe('123 Main St')
        ->and(settings('payment_method'))->toBe('cash');
});

test('saves paypal settings when paypal is a payment method', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => '[]',
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['paypal', 'cash'],
        'paypal_client_id' => 'client-123',
        'paypal_client_secret' => 'secret-456',
        'paypal_sandbox' => true,
        'webhook_url' => 'https://example.com/webhook',
        'webhook_secret' => 'whsec-789',
        'allergy_disclaimer' => 'Allergies noted.',
        'revenue_cap' => '250000',
        'cancellation_policy' => 'Cancel anytime.',
        'deposit_policy' => 'No deposit.',
        'refund_policy' => 'Full refund.',
        'pickup_policy' => 'Same day pickup.',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
    ];

    app(SaveTenantSettings::class)($data);

    expect(settings('paypal_client_id'))->toBe('client-123')
        ->and(settings('paypal_client_secret'))->toBe('secret-456')
        ->and(settings('paypal_sandbox'))->toBe('1')
        ->and(settings('webhook_url'))->toBe('https://example.com/webhook')
        ->and(settings('webhook_secret'))->toBe('whsec-789');
});

test('does not save paypal settings when paypal is not a payment method', function () {
    settings(['paypal_client_id' => 'old-value']);

    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => '[]',
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'allergy_disclaimer' => 'Allergies noted.',
        'revenue_cap' => '250000',
        'cancellation_policy' => 'Cancel anytime.',
        'deposit_policy' => 'No deposit.',
        'refund_policy' => 'Full refund.',
        'pickup_policy' => 'Same day pickup.',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
    ];

    app(SaveTenantSettings::class)($data);

    // Paypal settings were not updated, so old value remains
    expect(settings('paypal_client_id'))->toBe('old-value');
});

test('saves catering event types as a json array', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => '[]',
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'allergy_disclaimer' => '',
        'revenue_cap' => '250000',
        'cancellation_policy' => '',
        'deposit_policy' => '',
        'refund_policy' => '',
        'pickup_policy' => '',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
        'catering_event_types' => ['Kids Party', 'School Function', '   ', ''],
    ];

    app(SaveTenantSettings::class)($data);

    expect(json_decode(settings('catering_event_types'), true))
        ->toBe(['Kids Party', 'School Function']);
});
