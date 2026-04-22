<?php

$storefrontUrl = env('BROWSER_TEST_STOREFRONT_URL', 'http://browser-test.kneadit.test');

test('login page renders without JS errors and shows the page marker', function () use ($storefrontUrl) {
    visit("{$storefrontUrl}/account/login")
        ->assertVisible('[data-test="page-account-login-show"]')
        ->assertNoJavaScriptErrors();
});
