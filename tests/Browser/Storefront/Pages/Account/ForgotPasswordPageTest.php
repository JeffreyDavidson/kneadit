<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('forgot-password page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/forgot-password")
        ->assertVisible('[data-test="page-account-password-request"]')
        ->assertNoJavaScriptErrors();
});
