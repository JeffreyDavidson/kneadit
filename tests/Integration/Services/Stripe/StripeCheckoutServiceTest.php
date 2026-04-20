<?php

use App\Models\Orders\Order;
use App\Services\Stripe\StripeCheckoutService;
use App\Services\Stripe\StripeSettingsReader;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    config(['cashier.secret' => 'sk_test_fake_key']);
});

test('isEnabled returns false when stripe not in payment methods', function () {
    settings(['payment_methods' => json_encode(['paypal'])]);

    expect(app(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('isEnabled returns false when no connect id', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => null,
    ]);

    expect(app(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('isEnabled returns false when charges not enabled', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '0',
    ]);

    expect(app(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('isEnabled returns true when stripe fully configured', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => 'acct_test',
        'stripe_connect_charges_enabled' => '1',
    ]);

    expect(app(StripeSettingsReader::class)->isEnabled())->toBeTrue();
});

test('isEnabled returns false when payment methods is null', function () {
    settings(['payment_methods' => null]);

    expect(app(StripeSettingsReader::class)->isEnabled())->toBeFalse();
});

test('connectId returns stored connect id', function () {
    settings(['stripe_connect_id' => 'acct_123abc']);

    expect(app(StripeSettingsReader::class)->connectId())->toBe('acct_123abc');
});

test('connectId returns null when not set', function () {
    settings(['stripe_connect_id' => null]);

    expect(app(StripeSettingsReader::class)->connectId())->toBeNull();
});

test('redirectToCheckout returns null when total is zero', function () {
    $order = Order::factory()->create(['total' => 0]);

    $service = app(StripeCheckoutService::class);
    expect($service->redirectToCheckout($order))->toBeNull();
});

test('redirectToCheckout returns null when stripe not enabled', function () {
    settings(['payment_methods' => json_encode(['paypal'])]);

    $order = Order::factory()->create(['total' => 50.00]);

    $service = app(StripeCheckoutService::class);
    expect($service->redirectToCheckout($order))->toBeNull();
});

test('createCheckoutSession returns null when no connect id', function () {
    settings(['stripe_connect_id' => null]);

    $order = Order::factory()->create();

    $service = app(StripeCheckoutService::class);
    $result = $service->createCheckoutSession($order, 'https://success.com', 'https://cancel.com');

    expect($result)->toBeNull();
});

test('handleCheckoutComplete returns null when no connect id', function () {
    settings(['stripe_connect_id' => null]);

    $service = app(StripeCheckoutService::class);
    $result = $service->handleCheckoutComplete('cs_test_123');

    expect($result)->toBeNull();
});
