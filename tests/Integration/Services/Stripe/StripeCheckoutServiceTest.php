<?php

use App\Services\Stripe\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('isEnabled returns false when stripe not in payment methods', function () {
    settings(['payment_methods' => json_encode(['paypal'])]);

    expect(StripeCheckoutService::isEnabled())->toBeFalse();
});

test('isEnabled returns false when no connect id', function () {
    settings([
        'payment_methods' => json_encode(['stripe']),
        'stripe_connect_id' => null,
    ]);

    expect(StripeCheckoutService::isEnabled())->toBeFalse();
});
