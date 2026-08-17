<?php

use App\Filament\Pages\Platform\OnboardingSteps\PaymentsStep;
use App\Services\Settings\TenantSettings;

beforeEach(fn () => setUpTenantTest());

test('payment step stores payment methods as json array', function () {
    PaymentsStep::save([
        'payment_methods' => ['cash', 'paypal', 'stripe'],
        'paypal_client_id' => 'test_id',
        'paypal_client_secret' => 'test_secret',
        'paypal_sandbox' => true,
    ]);

    $stored = settings('payment_methods');

    if (! is_string($stored)) {
        throw new RuntimeException('Expected payment methods to be stored as JSON.');
    }

    $decoded = json_decode($stored, true);
    expect($decoded)->toBeArray()->toContain('cash')->toContain('paypal')->toContain('stripe');
});

test('payment step sets legacy payment method to first value', function () {
    PaymentsStep::save([
        'payment_methods' => ['stripe', 'cash'],
        'paypal_client_id' => '',
        'paypal_client_secret' => '',
        'paypal_sandbox' => false,
    ]);

    expect(settings('payment_method'))->toBe('stripe');
});

test('payment step defaults to cash when empty', function () {
    PaymentsStep::save([
        'payment_methods' => [],
        'paypal_client_id' => '',
        'paypal_client_secret' => '',
        'paypal_sandbox' => false,
    ]);

    expect(settings('payment_method'))->toBe('cash');
});

test('payment methods defaults to cash when none stored', function () {
    $defaults = PaymentsStep::defaults(resolve(TenantSettings::class));

    expect($defaults['payment_methods'])->toBe(['cash']);
});
