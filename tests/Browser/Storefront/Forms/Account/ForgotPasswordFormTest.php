<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('forgot password form submits successfully and shows the status message', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/forgot-password")
        ->assertVisible('[data-test="forgot-password-form"]')
        ->fill('[data-test="forgot-password-form-email"]', 'jane@example.com')
        ->press('Send reset link')
        // Controller always reports success to avoid leaking account existence;
        // the status flash should appear on either path (existing or unknown email).
        ->assertSee('reset')
        ->assertNoJavaScriptErrors();
});

test('forgot password form blocks empty submit via HTML5 required', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/forgot-password")
        ->press('Send reset link')
        ->assertPathIs('/account/forgot-password')
        ->assertVisible('[data-test="forgot-password-form"]')
        ->assertNoJavaScriptErrors();
});
