<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('gift card balance form surfaces a not-found error for an unknown code', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/gift-cards")
        ->assertVisible('[data-test="gift-card-balance-form"]')
        ->fill('[data-test="gift-card-balance-form-code"]', 'FAKE-FAKE-FAKE-FAKE')
        ->click('Check Balance')
        ->assertSee('Gift card not found')
        ->assertNoJavaScriptErrors();
});
