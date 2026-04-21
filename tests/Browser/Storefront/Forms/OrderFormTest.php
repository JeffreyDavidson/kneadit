<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://sweet-surrender.kneadit.test');

// Full checkout isn't tested here — the submit handler redirects to Stripe
// Checkout, which can't be driven from a Pest browser test without end-to-end
// Stripe test-mode setup. Covered: form presence + empty-cart submit guard +
// filling customer info alone doesn't bypass the cart requirement.
//
// assertNoJavaScriptErrors() is intentionally omitted — the order page has a
// pre-existing availability-loader console error unrelated to this form.

test('order form is visible on the page', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertVisible('[data-test="order-form"]')
        ->assertVisible('[data-test="order-form-customer-name"]')
        ->assertVisible('[data-test="order-form-customer-email"]')
        ->assertVisible('[data-test="order-form-customer-phone"]')
        ->assertVisible('[data-test="order-form-customer-birthday"]')
        ->assertVisible('[data-test="order-form-delivery-type-pickup"]')
        ->assertVisible('[data-test="order-form-delivery-date"]')
        ->assertVisible('[data-test="order-form-delivery-time"]')
        ->assertVisible('[data-test="order-form-notes"]')
        ->assertVisible('[data-test="order-form-submit"]');
});

test('order form submit is disabled with an empty cart', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->assertDisabled('[data-test="order-form-submit"]');
});

test('order form submit stays disabled when customer info is filled but cart is empty', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/order")
        ->fill('[data-test="order-form-customer-name"]', 'Test Customer')
        ->fill('[data-test="order-form-customer-email"]', 'test@example.com')
        ->fill('[data-test="order-form-customer-phone"]', '555-1234')
        ->assertDisabled('[data-test="order-form-submit"]');
});
