<?php

$storefrontUrl = Illuminate\Support\Facades\Config::string('browser-testing.storefront_url');

test('loyalty lookup form blocks empty submit via HTML5 required', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/rewards")
        ->press('Check Balance')
        ->assertPathIs('/rewards')
        ->assertVisible('[data-test="loyalty-lookup-form"]')
        ->assertNoJavaScriptErrors();
});

test('loyalty lookup form submits with an unknown email and renders the rewards page', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/rewards")
        ->fill('[data-test="loyalty-lookup-form-email"]', 'nobody@example.com')
        ->press('Check Balance')
        // Controller renders the rewards page with a customerNotFound flag for unknown emails;
        // either way the form remains and the page stays under /rewards-related rendering.
        ->assertVisible('[data-test="loyalty-lookup-form"]')
        ->assertNoJavaScriptErrors();
});
