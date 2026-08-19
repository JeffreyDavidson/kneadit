<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

// Full purchase flow isn't tested here — the form's submit handler redirects to
// Stripe Checkout, which can't be driven from a Pest browser test without
// end-to-end Stripe test-mode setup. Covered: form presence + empty-state
// button guard.

test('gift card purchase form is visible on the page', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gift-cards")
        ->assertVisible('[data-test="gift-card-purchase-form"]')
        ->assertVisible('[data-test="gift-card-purchase-form-purchaser-name"]')
        ->assertVisible('[data-test="gift-card-purchase-form-purchaser-email"]')
        ->assertVisible('[data-test="gift-card-purchase-form-submit"]')
        ->assertNoJavaScriptErrors();
});

test('gift card purchase submit button is disabled before required fields are filled', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gift-cards")
        ->assertDisabled('[data-test="gift-card-purchase-form-submit"]')
        ->assertNoJavaScriptErrors();
});
