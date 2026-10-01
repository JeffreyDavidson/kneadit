<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

// Gift cards are sold in person until a paid online checkout exists, so the
// page offers the in-store notice and the balance check, not a purchase form.

test('gift cards page shows the in-store notice instead of a purchase form', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gift-cards")
        ->assertVisible('[data-test="gift-card-in-store-notice"]')
        ->assertMissing('[data-test="gift-card-purchase-form"]')
        ->assertVisible('[data-test="gift-card-balance-form"]')
        ->assertNoJavaScriptErrors();
});

test('gift card balance check button is disabled until a code is entered', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gift-cards")
        ->assertDisabled('[data-test="gift-card-balance-form-submit"]')
        ->assertNoJavaScriptErrors();
});
