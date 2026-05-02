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
        'delivery_fee_tiers' => [],
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

    resolve(SaveTenantSettings::class)($data);

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
        'delivery_fee_tiers' => [],
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

    resolve(SaveTenantSettings::class)($data);

    expect(settings('paypal_client_id'))->toBe('client-123')
        ->and(settings('paypal_client_secret'))->toBe('secret-456')
        ->and(settings('paypal_sandbox'))->toBe('1')
        ->and(settings('webhook_url'))->toBe('https://example.com/webhook')
        ->and(settings('webhook_secret'))->toBe('whsec-789');
});

test('persists empty paypal credentials when not provided in $data', function () {
    // The action now always writes paypal_* (defaults to '' if not provided)
    // so it mirrors the webhook fix and is safe for callers that omit the
    // keys. The form path is unaffected — Filament always sends the property
    // values even when the PayPal section is hidden.
    settings(['paypal_client_id' => 'old-value']);

    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
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

    resolve(SaveTenantSettings::class)($data);

    expect(settings('paypal_client_id'))->toBe('')
        ->and(settings('paypal_client_secret'))->toBe('')
        ->and(settings('paypal_sandbox'))->toBe('0');
});

test('persists paypal credentials regardless of whether paypal is in payment_methods', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'paypal_client_id' => 'still-here',
        'paypal_client_secret' => 'still-here-secret',
        'paypal_sandbox' => true,
        'allergy_disclaimer' => '',
        'revenue_cap' => '250000',
        'cancellation_policy' => '',
        'deposit_policy' => '',
        'refund_policy' => '',
        'pickup_policy' => '',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
    ];

    resolve(SaveTenantSettings::class)($data);

    expect(settings('paypal_client_id'))->toBe('still-here')
        ->and(settings('paypal_client_secret'))->toBe('still-here-secret')
        ->and(settings('paypal_sandbox'))->toBe('1');
});

test('saves catering event types as a json array', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
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

    resolve(SaveTenantSettings::class)($data);

    expect(json_decode(settings('catering_event_types'), true))
        ->toBe(['Kids Party', 'School Function']);
});

test('saves webhook settings even when paypal is not a payment method', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'webhook_url' => 'https://hooks.example.com/test',
        'webhook_secret' => 'manually-provided-secret',
        'allergy_disclaimer' => '',
        'revenue_cap' => '250000',
        'cancellation_policy' => '',
        'deposit_policy' => '',
        'refund_policy' => '',
        'pickup_policy' => '',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
    ];

    resolve(SaveTenantSettings::class)($data);

    expect(settings('webhook_url'))->toBe('https://hooks.example.com/test')
        ->and(settings('webhook_secret'))->toBe('manually-provided-secret');
});

test('auto-generates webhook secret when url is set without one', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'webhook_url' => 'https://hooks.example.com/test',
        'webhook_secret' => '',
        'allergy_disclaimer' => '',
        'revenue_cap' => '250000',
        'cancellation_policy' => '',
        'deposit_policy' => '',
        'refund_policy' => '',
        'pickup_policy' => '',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
    ];

    resolve(SaveTenantSettings::class)($data);

    expect(settings('webhook_url'))->toBe('https://hooks.example.com/test')
        ->and(strlen((string) settings('webhook_secret')))->toBe(40);
});

test('preserves an explicitly provided secret instead of generating one', function () {
    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
        'repeat_reminders_enabled' => true,
        'birthday_program_enabled' => false,
        'payment_methods' => ['cash'],
        'webhook_url' => 'https://hooks.example.com/test',
        'webhook_secret' => 'caller-supplied-secret',
        'allergy_disclaimer' => '',
        'revenue_cap' => '250000',
        'cancellation_policy' => '',
        'deposit_policy' => '',
        'refund_policy' => '',
        'pickup_policy' => '',
        'additional_terms' => '',
        'show_policies_on_storefront' => false,
    ];

    resolve(SaveTenantSettings::class)($data);

    expect(settings('webhook_secret'))->toBe('caller-supplied-secret');
});

test('saves order journey steps as JSON', function () {
    $steps = [
        ['title' => 'Confirmed', 'description' => 'Order received.'],
        ['title' => 'Baking', 'description' => 'Fresh from the oven.'],
        ['title' => 'Ready', 'description_delivery' => 'On the way.', 'description_pickup' => 'Ready for pickup.'],
    ];

    $data = [
        'store_name' => 'Test Bakery',
        'store_email' => 'info@test.com',
        'store_phone' => '555-1234',
        'store_address' => '123 Main St',
        'default_daily_capacity' => 10,
        'minimum_order_lead_hours' => 24,
        'delivery_fee_tiers' => [],
        'repeat_reminders_enabled' => false,
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
        'order_journey_steps' => $steps,
    ];

    resolve(SaveTenantSettings::class)($data);

    expect(json_decode(settings('order_journey_steps'), true))->toEqual($steps);
});
